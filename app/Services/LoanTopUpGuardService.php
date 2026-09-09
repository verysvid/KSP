<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LoanTopUpGuardService
{
    public static function blockingStatuses(): array
    {
        return [Loan::STATUS_SUBMITTED, Loan::STATUS_APPROVED];
    }

    public static function hasBlockingTopUpForSource(int $sourceLoanId): bool
    {
        return Loan::query()
            ->where('topup_from_loan_id', $sourceLoanId)
            ->whereIn('status', self::blockingStatuses())
            ->exists();
    }

    public static function assertLoanPaymentAllowed(Loan $loan): void
    {
        if (!self::hasBlockingTopUpForSource((int) $loan->id)) return;

        throw ValidationException::withMessages([
            'loan' => 'Pembayaran angsuran tidak dapat dilakukan karena pinjaman sedang dalam proses TopUp. Selesaikan proses TopUp sampai dengan Pencairan atau lakukan Reject terlebih dahulu.',
        ]);
    }

    public static function blockingTopUpsForBranch(int $branchId): Collection
    {
        return Loan::query()
            ->with('member:id,name')
            ->where('branch_id', $branchId)
            ->whereNotNull('topup_from_loan_id')
            ->whereIn('status', self::blockingStatuses())
            ->orderBy('application_date')
            ->orderBy('id')
            ->get(['id','branch_id','member_id','loan_no','topup_from_loan_id','status','application_date']);
    }

    public static function assertBulkAllowedForBranch(int $branchId): void
    {
        $items = self::blockingTopUpsForBranch($branchId);

        if ($items->isEmpty()) return;

        throw ValidationException::withMessages([
            'topup' => 'Ada Pengajuan TopUp yang belum selesai diproses. Selesaikan proses sampai dengan Pencairan atau Reject terlebih dahulu.',
        ]);
    }
}
