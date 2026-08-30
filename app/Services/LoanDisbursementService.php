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
            $oldValues = $loan->toArray();

            $disbursement = LoanDisbursement::create([
                'loan_id' => $loan->id,
                'branch_id' => $loan->branch_id,
                'disbursement_date' => $data['disbursement_date'],
                'amount' => $loan->principal_amount,
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

            $this->auditLogService->log(
                'UPDATE',
                $loan,
                "Mencairkan pinjaman {$loan->loan_no}",
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
