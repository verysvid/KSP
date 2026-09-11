<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanEarlyRepayment;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LoanEarlyRepaymentGuardService
{
    public function eligibility(Loan $loan): array
    {
        if ($loan->status !== Loan::STATUS_ACTIVE) {
            return [
                'eligible' => false,
                'message' => 'Pelunasan Dini hanya tersedia untuk pinjaman berstatus ACTIVE.',
            ];
        }

        if ((bool) $loan->is_early_repayment) {
            return [
                'eligible' => false,
                'message' => 'Pinjaman sedang dalam proses verifikasi Pelunasan Dini.',
            ];
        }

        if ((float) $loan->outstanding_principal <= 0) {
            return [
                'eligible' => false,
                'message' => 'Pinjaman tidak memiliki sisa pokok.',
            ];
        }

        if (!$loan->installments()->exists()) {
            return [
                'eligible' => false,
                'message' => 'Jadwal angsuran pinjaman belum tersedia.',
            ];
        }

        $hasOpenTopUp = Loan::query()
            ->where('topup_from_loan_id', $loan->id)
            ->whereIn('status', [
                Loan::STATUS_DRAFT,
                Loan::STATUS_SUBMITTED,
                Loan::STATUS_APPROVED,
            ])
            ->exists();

        if ($hasOpenTopUp) {
            return [
                'eligible' => false,
                'message' => 'Pelunasan Dini tidak dapat diajukan karena masih ada proses TopUp yang belum selesai.',
            ];
        }

        $hasSubmittedRequest = LoanEarlyRepayment::withoutGlobalScopes()
            ->where('loan_id', $loan->id)
            ->where('status', LoanEarlyRepayment::STATUS_SUBMITTED)
            ->exists();

        if ($hasSubmittedRequest) {
            return [
                'eligible' => false,
                'message' => 'Pengajuan Pelunasan Dini untuk pinjaman ini masih menunggu verifikasi.',
            ];
        }

        return [
            'eligible' => true,
            'message' => null,
        ];
    }

    public function ensureEligible(Loan $loan): void
    {
        $result = $this->eligibility($loan);

        if (!$result['eligible']) {
            throw ValidationException::withMessages([
                'early_repayment' => $result['message'] ?? 'Pinjaman belum dapat diajukan untuk Pelunasan Dini.',
            ]);
        }
    }

    public static function assertLoanPaymentAllowed(Loan $loan): void
    {
        if (
            $loan->status === Loan::STATUS_ACTIVE
            && (bool) $loan->is_early_repayment
        ) {
            throw ValidationException::withMessages([
                'loan' => 'Pembayaran angsuran tidak dapat dilakukan karena Pelunasan Dini sedang menunggu verifikasi/approval.',
            ]);
        }
    }

    public static function blockingForBranch(int $branchId): Collection
    {
        return LoanEarlyRepayment::withoutGlobalScopes()
            ->with([
                'loan:id,branch_id,member_id,loan_no,status,is_early_repayment',
                'loan.member:id,name',
            ])
            ->where('branch_id', $branchId)
            ->where('status', LoanEarlyRepayment::STATUS_SUBMITTED)
            ->whereHas('loan', function ($query) {
                $query->where('status', Loan::STATUS_ACTIVE)
                    ->where('is_early_repayment', true);
            })
            ->orderBy('request_date')
            ->orderBy('id')
            ->get();
    }

    public static function assertBulkAllowedForBranch(int $branchId): void
    {
        $items = self::blockingForBranch($branchId);

        if ($items->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'early_repayment' => 'Ada Pelunasan Dini yang belum diverifikasi/approve. Harap segera dicek dan ditindaklanjuti sebelum menjalankan Transaksi Bulk.',
        ]);
    }
}
