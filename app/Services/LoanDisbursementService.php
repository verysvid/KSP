<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanDisbursementService
{
    public function __construct(
        protected LoanCalculatorService $calculator,
        protected JournalService $journalService,
        protected AuditLogService $auditLogService
    ) {
    }

    public function disburse(
        Loan $loan,
        array $data,
        int $userId
    ): LoanDisbursement {
        if ($loan->status !== Loan::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'loan' => 'Hanya pinjaman berstatus APPROVED yang dapat dicairkan.',
            ]);
        }

        if ($loan->disbursement()->exists()) {
            throw ValidationException::withMessages([
                'loan' => 'Pinjaman ini sudah pernah dicairkan.',
            ]);
        }

        $loan->loadMissing('loanType');

        if (!$loan->loanType?->receivable_account_id) {
            throw ValidationException::withMessages([
                'loan' => 'Akun piutang belum dipetakan pada Jenis Pinjaman.',
            ]);
        }

        $cashAccount = Account::query()
            ->whereKey($data['cash_account_id'])
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->first();

        if (!$cashAccount) {
            throw ValidationException::withMessages([
                'cash_account_id' => 'Akun Kas/Bank tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($loan, $data, $userId, $cashAccount) {
            $loan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $loan->loadMissing('loanType');
            $oldValues = $loan->toArray();

            $oldLoan = null;
            $oldLoanValues = [];
            $cashDisbursementAmount = round((float) $loan->principal_amount, 2);

            if ($loan->is_topup) {
                if (!$loan->topup_from_loan_id || !$loan->old_loan_no || (float) $loan->topup_amount <= 0) {
                    throw ValidationException::withMessages([
                        'loan' => 'Data referensi TopUp tidak lengkap.',
                    ]);
                }

                $oldLoan = Loan::query()
                    ->lockForUpdate()
                    ->findOrFail($loan->topup_from_loan_id);

                if ($oldLoan->status !== Loan::STATUS_ACTIVE) {
                    throw ValidationException::withMessages([
                        'loan' => 'Pinjaman lama sudah tidak ACTIVE. Pencairan TopUp dibatalkan.',
                    ]);
                }

                if (
                    (int) $oldLoan->branch_id !== (int) $loan->branch_id
                    || (int) $oldLoan->member_id !== (int) $loan->member_id
                    || (int) $oldLoan->loan_type_id !== (int) $loan->loan_type_id
                ) {
                    throw ValidationException::withMessages([
                        'loan' => 'Data pinjaman lama tidak sesuai dengan pengajuan TopUp.',
                    ]);
                }

                $snapshotOutstanding = round(
                    (float) $loan->principal_amount - (float) $loan->topup_amount,
                    2
                );
                $currentOutstanding = round((float) $oldLoan->outstanding_principal, 2);

                if (abs($snapshotOutstanding - $currentOutstanding) > 0.01) {
                    throw ValidationException::withMessages([
                        'loan' => 'Sisa pokok pinjaman lama berubah sejak TopUp diajukan. Edit draft/ajukan ulang TopUp agar nominal total sesuai saldo terbaru.',
                    ]);
                }

                $oldLoanValues = $oldLoan->toArray();
                $cashDisbursementAmount = round((float) $loan->topup_amount, 2);
            }

            $disbursement = LoanDisbursement::create([
                'loan_id' => $loan->id,
                'branch_id' => $loan->branch_id,
                'disbursement_date' => $data['disbursement_date'],
                'amount' => $cashDisbursementAmount,
                'cash_account_id' => $cashAccount->id,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $schedule = $this->calculator->buildSchedule(
                $loan,
                $data['disbursement_date']
            );

            foreach ($schedule as $row) {
                $loan->installments()->create($row);
            }

            $totalInterest = round(array_sum(array_column($schedule, 'interest_amount')), 2);
            $totalInstallment = round(array_sum(array_column($schedule, 'installment_amount')), 2);

            if ($loan->is_topup && $oldLoan) {
                $oldOutstanding = round((float) $oldLoan->outstanding_principal, 2);

                $journalLines = [
                    [
                        'account_id' => $loan->loanType->receivable_account_id,
                        'debit' => (float) $loan->principal_amount,
                        'credit' => 0,
                        'description' => "Piutang TopUp {$loan->loan_no}",
                    ],
                    [
                        'account_id' => $loan->loanType->receivable_account_id,
                        'debit' => 0,
                        'credit' => $oldOutstanding,
                        'description' => "Pelunasan pokok lama {$oldLoan->loan_no} via TopUp",
                    ],
                    [
                        'account_id' => $cashAccount->id,
                        'debit' => 0,
                        'credit' => $cashDisbursementAmount,
                        'description' => "Pencairan dana TopUp {$loan->loan_no}",
                    ],
                ];

                $journal = $this->journalService->create(
                    branchId: $loan->branch_id,
                    journalDate: $data['disbursement_date'],
                    description: "Pencairan TopUp {$loan->loan_no} dari {$oldLoan->loan_no}",
                    referenceType: LoanDisbursement::class,
                    referenceId: $disbursement->id,
                    lines: $journalLines,
                    createdBy: $userId
                );

                // Angsuran lama yang belum lunas ditutup administratif karena pokoknya
                // direfinance ke pinjaman baru. Tidak dibuat LoanPayment palsu dan bunga
                // masa depan tidak diakui sebagai pendapatan.
                $oldLoan->installments()
                    ->where('status', '!=', 'PAID')
                    ->update([
                        'status' => 'PAID',
                        'principal_paid' => DB::raw('principal_amount'),
                        'is_overdue' => false,
                        'days_overdue' => 0,
                        'overdue_calculated_at' => now(),
                        'paid_at' => now(),
                    ]);

                $oldLoan->update([
                    'status' => Loan::STATUS_PAID_OFF,
                    'outstanding_principal' => 0,
                    'outstanding_interest' => 0,
                    'updated_by' => $userId,
                ]);

                $this->auditLogService->log(
                    'UPDATE',
                    $oldLoan,
                    "Melunasi pinjaman {$oldLoan->loan_no} melalui pencairan TopUp {$loan->loan_no}",
                    $oldLoanValues,
                    $oldLoan->fresh()->toArray()
                );
            } else {
                $journal = $this->journalService->create(
                    branchId: $loan->branch_id,
                    journalDate: $data['disbursement_date'],
                    description: "Pencairan pinjaman {$loan->loan_no}",
                    referenceType: LoanDisbursement::class,
                    referenceId: $disbursement->id,
                    lines: [
                        [
                            'account_id' => $loan->loanType->receivable_account_id,
                            'debit' => (float) $loan->principal_amount,
                            'credit' => 0,
                            'description' => "Piutang {$loan->loan_no}",
                        ],
                        [
                            'account_id' => $cashAccount->id,
                            'debit' => 0,
                            'credit' => (float) $loan->principal_amount,
                            'description' => "Pencairan {$loan->loan_no}",
                        ],
                    ],
                    createdBy: $userId
                );
            }

            $disbursement->update([
                'journal_entry_id' => $journal->id,
            ]);

            $loan->update([
                'status' => Loan::STATUS_ACTIVE,
                'disbursed_at' => now(),
                'disbursed_by' => $userId,
                'total_principal' => $loan->principal_amount,
                'total_interest' => $totalInterest,
                'total_installment' => $totalInstallment,
                'outstanding_principal' => $loan->principal_amount,
                'outstanding_interest' => $totalInterest,
                'updated_by' => $userId,
            ]);

            $actionDescription = $loan->is_topup
                ? "Mencairkan TopUp {$loan->loan_no} dan melunasi pinjaman lama {$loan->old_loan_no}"
                : "Mencairkan pinjaman {$loan->loan_no}";

            $this->auditLogService->log(
                'UPDATE',
                $loan,
                $actionDescription,
                $oldValues,
                $loan->fresh()->toArray()
            );

            return $disbursement->fresh([
                'cashAccount',
                'journalEntry',
            ]);
        });
    }
}
