<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Validation\ValidationException;

class LoanTopUpService
{
    public function eligibility(Loan $loan): array
    {
        if ($loan->status !== Loan::STATUS_ACTIVE) {
            return ['eligible' => false, 'message' => 'TopUp hanya tersedia untuk pinjaman berstatus ACTIVE.'];
        }

        $tenor = max(1, (int) $loan->tenor_months);
        $requiredPaid = (int) ceil($tenor / 2);
        $paidCount = $loan->installments()->where('status', 'PAID')->count();
        $installmentCount = $loan->installments()->count();

        if ($installmentCount === 0) {
            return ['eligible' => false, 'message' => 'Jadwal angsuran pinjaman belum tersedia.'];
        }

        if ($paidCount < $requiredPaid) {
            return [
                'eligible' => false,
                'message' => "TopUp tersedia setelah minimal {$requiredPaid} angsuran lunas. Saat ini baru {$paidCount} angsuran lunas.",
                'paid_count' => $paidCount,
                'required_paid' => $requiredPaid,
            ];
        }

        if ((float) $loan->outstanding_principal <= 0) {
            return ['eligible' => false, 'message' => 'Pinjaman tidak memiliki sisa pokok.'];
        }

        $blockingStatuses = [
            Loan::STATUS_DRAFT,
            Loan::STATUS_SUBMITTED,
            Loan::STATUS_APPROVED,
            Loan::STATUS_ACTIVE,
        ];

        $hasOpenTopUp = Loan::query()
            ->where('topup_from_loan_id', $loan->id)
            ->whereIn('status', $blockingStatuses)
            ->exists();

        if ($hasOpenTopUp) {
            return ['eligible' => false, 'message' => 'Pinjaman ini sudah memiliki proses TopUp yang masih berjalan.'];
        }

        return [
            'eligible' => true,
            'message' => null,
            'paid_count' => $paidCount,
            'required_paid' => $requiredPaid,
            'remaining_tenor' => $loan->installments()->where('status', '!=', 'PAID')->count(),
        ];
    }

    public function ensureEligible(Loan $loan): array
    {
        $result = $this->eligibility($loan);

        if (!$result['eligible']) {
            throw ValidationException::withMessages([
                'topup' => $result['message'] ?? 'Pinjaman belum memenuhi syarat TopUp.',
            ]);
        }

        return $result;
    }

    public function buildSnapshot(Loan $loan): array
    {
        $loan->loadMissing(['branch', 'member', 'loanType', 'installments']);

        $lastDueDate = $loan->installments
            ->sortByDesc(fn ($item) => $item->due_date?->format('Y-m-d'))
            ->first()?->due_date;

        return [
            'loan_no' => $loan->loan_no,
            'principal_amount' => (float) $loan->principal_amount,
            'last_due_date' => $lastDueDate,
            'tenor_months' => (int) $loan->tenor_months,
            'outstanding_principal' => (float) $loan->outstanding_principal,
            'remaining_tenor' => $loan->installments->where('status', '!=', 'PAID')->count(),
        ];
    }

    public function buildNotes(Loan $loan): string
    {
        $snapshot = $this->buildSnapshot($loan);
        $lastDue = $snapshot['last_due_date']?->format('d/m/Y') ?? '-';

        return sprintf(
            'Pengajuan TopUp dari Pinjaman %s | Jumlah Pinjaman: Rp %s | Tanggal Terakhir Jatuh Tempo: %s | Total Tenor: %d bulan | Sisa Pokok: Rp %s | Sisa Tenor: %d bulan.',
            $snapshot['loan_no'],
            number_format($snapshot['principal_amount'], 0, ',', '.'),
            $lastDue,
            $snapshot['tenor_months'],
            number_format($snapshot['outstanding_principal'], 0, ',', '.'),
            $snapshot['remaining_tenor']
        );
    }
}
