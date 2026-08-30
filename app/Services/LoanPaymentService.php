<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\LoanPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanPaymentService
{
    public function __construct(
        protected JournalService $journalService,
        protected AuditLogService $auditLogService,
		protected LoanPenaltyService $loanPenaltyService
    ) {
    }

    public function payInstallment(
        Loan $loan,
        LoanInstallment $installment,
        array $data,
        int $userId
    ): LoanPayment {
        if ($loan->status !== Loan::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'loan' => 'Hanya pinjaman berstatus ACTIVE yang dapat menerima pembayaran.',
            ]);
        }

        if ($installment->loan_id !== $loan->id) {
            throw ValidationException::withMessages([
                'installment' => 'Angsuran tidak termasuk dalam pinjaman ini.',
            ]);
        }

        if ($installment->status === 'PAID') {
            throw ValidationException::withMessages([
                'installment' => 'Angsuran ini sudah lunas.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Current business rule: full payment only
        |--------------------------------------------------------------------------
        | Struktur database tetap menyiapkan principal_paid/interest_paid/
        | penalty_paid untuk kemungkinan partial payment di masa depan.
        */
        if (
            (float) $installment->principal_paid > 0
            || (float) $installment->interest_paid > 0
            || (float) $installment->penalty_paid > 0
        ) {
            throw ValidationException::withMessages([
                'installment' => 'Angsuran memiliki pembayaran parsial. Step ini hanya menerima pelunasan penuh.',
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

        $loan->loadMissing('loanType');

        if (!$loan->loanType?->receivable_account_id) {
            throw ValidationException::withMessages([
                'loan' => 'Akun piutang belum dipetakan pada Jenis Pinjaman.',
            ]);
        }

		$penaltyResult = $this->loanPenaltyService->calculateForInstallment(
			$installment,
			$data['payment_date']
		);

		$principal = round((float)$installment->principal_amount, 2);
		$interest = round((float)$installment->interest_amount, 2);
		$penalty = round((float)$penaltyResult['penalty_amount'], 2);
		$total = round($principal + $interest + $penalty, 2);

        if (
            (float) $interest > 0
            && !$loan->loanType?->interest_income_account_id
        ) {
            throw ValidationException::withMessages([
                'loan' => 'Akun pendapatan bunga belum dipetakan pada Jenis Pinjaman.',
            ]);
        }

        if (
            (float) $penalty > 0
            && !$loan->loanType?->penalty_income_account_id
        ) {
            throw ValidationException::withMessages([
                'loan' => 'Akun pendapatan denda belum dipetakan pada Jenis Pinjaman.',
            ]);
        }

        return DB::transaction(function () use (
            $loan,
            $installment,
            $data,
            $userId,
            $cashAccount,
			$penaltyResult,
			$principal,
			$interest,
			$penalty,
			$total
        ) {

            $payment = LoanPayment::create([
                'loan_id' => $loan->id,
                'loan_installment_id' => $installment->id,
                'branch_id' => $loan->branch_id,
                'payment_no' => $this->generatePaymentNo($loan->branch_id),
                'payment_date' => $data['payment_date'],
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'penalty_amount' => $penalty,
                'total_amount' => $total,
                'cash_account_id' => $cashAccount->id,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $journalLines = [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => $total,
                    'credit' => 0,
                    'description' => "Pembayaran {$payment->payment_no}",
                ],
                [
                    'account_id' => $loan->loanType->receivable_account_id,
                    'debit' => 0,
                    'credit' => $principal,
                    'description' => "Pokok angsuran {$loan->loan_no}",
                ],
            ];

            if ($interest > 0) {
                $journalLines[] = [
                    'account_id' => $loan->loanType->interest_income_account_id,
                    'debit' => 0,
                    'credit' => $interest,
                    'description' => "Bunga angsuran {$loan->loan_no}",
                ];
            }

            if ($penalty > 0) {
                $journalLines[] = [
                    'account_id' => $loan->loanType->penalty_income_account_id,
                    'debit' => 0,
                    'credit' => $penalty,
                    'description' => "Denda angsuran {$loan->loan_no}",
                ];
            }

            $journal = $this->journalService->create(
                branchId: $loan->branch_id,
                journalDate: $data['payment_date'],
                description: "Pembayaran angsuran #{$installment->installment_no} {$loan->loan_no}",
                referenceType: LoanPayment::class,
                referenceId: $payment->id,
                lines: $journalLines,
                createdBy: $userId
            );

            $payment->update([
                'journal_entry_id' => $journal->id,
            ]);

			$installment->update([
				'penalty_amount' => $penalty,
				'principal_paid' => $principal,
				'interest_paid' => $interest,
				'penalty_paid' => $penalty,
				'status' => 'PAID',
				'is_overdue' => $penaltyResult['is_overdue'],
				'days_overdue' => $penaltyResult['days_overdue'],
				'overdue_calculated_at' => now(),
				'paid_at' => now(),
			]);

            $oldValues = $loan->toArray();

            $newOutstandingPrincipal = max(
                0,
                round((float) $loan->outstanding_principal - $principal, 2)
            );

            $newOutstandingInterest = max(
                0,
                round((float) $loan->outstanding_interest - $interest, 2)
            );

            $remainingInstallments = $loan->installments()
                ->where('status', '!=', 'PAID')
                ->count();

            $loan->update([
                'outstanding_principal' => $newOutstandingPrincipal,
                'outstanding_interest' => $newOutstandingInterest,
                'status' => $remainingInstallments === 0
                    ? Loan::STATUS_PAID_OFF
                    : Loan::STATUS_ACTIVE,
                'updated_by' => $userId,
            ]);

            $this->auditLogService->log(
                'CREATE',
                $payment,
                "Pembayaran angsuran #{$installment->installment_no} pinjaman {$loan->loan_no}",
                [],
                $payment->fresh()->toArray()
            );

            $this->auditLogService->log(
                'UPDATE',
                $loan,
                "Memperbarui saldo pinjaman setelah pembayaran {$payment->payment_no}",
                $oldValues,
                $loan->fresh()->toArray()
            );

            return $payment->fresh([
                'cashAccount',
                'journalEntry',
                'installment',
            ]);
        });
    }

    protected function generatePaymentNo(int $branchId): string
    {
        $prefix = 'PAY-' . now()->format('Ym');

        $lastPayment = LoanPayment::query()
            ->where('branch_id', $branchId)
            ->where('payment_no', 'like', $prefix . '-%')
            ->latest('id')
            ->first();

        $sequence = 0;

        if ($lastPayment) {
            $parts = explode('-', $lastPayment->payment_no);
            $sequence = (int) end($parts);
        }

        return sprintf(
            '%s-%06d',
            $prefix,
            $sequence + 1
        );
    }
}
