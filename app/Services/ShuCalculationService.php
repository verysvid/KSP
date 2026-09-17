<?php

namespace App\Services;

use App\Models\Member;
use App\Models\ShuPeriod;
use App\Models\YearClosing;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShuCalculationService
{

    public function calculate(ShuPeriod $period): ShuPeriod
    {
        if ($period->isLocked()) {
            throw ValidationException::withMessages([
                'shu' => 'SHU yang sudah difinalisasi/dibayar tidak dapat dihitung ulang.',
            ]);
        }

        return DB::transaction(function () use ($period) {
            /** @var ShuPeriod $locked */
            $locked = ShuPeriod::query()->whereKey($period->id)->lockForUpdate()->firstOrFail();
            $locked->load(['allocations', 'savingTypes']);

            $this->validateConfiguration($locked);

            $start = CarbonImmutable::parse($locked->start_date)->startOfDay();
            $end = CarbonImmutable::parse($locked->end_date)->startOfDay();

            $closing = YearClosing::withoutGlobalScopes()
                ->where('branch_id', $locked->branch_id)
                ->where('year', $locked->year)
                ->where('status', YearClosing::STATUS_CLOSED)
                ->first();

            if (! $closing) {
                throw ValidationException::withMessages([
                    'shu' => 'Tahun buku belum CLOSED. Lakukan Tutup Buku terlebih dahulu sebelum menghitung SHU.',
                ]);
            }

            if (
                $start->toDateString() !== $closing->start_date->toDateString()
                || $end->toDateString() !== $closing->end_date->toDateString()
            ) {
                throw ValidationException::withMessages([
                    'shu' => 'Periode SHU harus sama dengan periode Tutup Buku: '
                        . $closing->start_date->format('d/m/Y') . ' - '
                        . $closing->end_date->format('d/m/Y') . '.',
                ]);
            }

            $netShu = round((float) $closing->net_shu, 2);

            if ($netShu <= 0) {
                throw ValidationException::withMessages([
                    'shu' => 'Snapshot Tutup Buku tidak menghasilkan SHU positif. Pembagian SHU anggota tidak dapat diproses.',
                ]);
            }

            $locked->update([
                'year_closing_id' => $closing->id,
                'total_revenue' => round((float) $closing->total_revenue, 2),
                'total_expense' => round((float) $closing->total_expense, 2),
                'net_shu' => $netShu,
            ]);

            $allocations = $locked->allocations()->orderBy('sort_order')->orderBy('id')->get();
            $allocationAmounts = [];
            $running = 0.0;

            foreach ($allocations as $index => $allocation) {
                $amount = $index === $allocations->count() - 1
                    ? round($netShu - $running, 2)
                    : round($netShu * ((float) $allocation->percentage / 100), 2);

                $allocationAmounts[$allocation->id] = $amount;
                $running += $amount;
                $allocation->update(['amount' => $amount]);
            }

            $memberAllocation = $allocations->firstWhere('is_member_pool', true);
            $memberPool = (float) ($allocationAmounts[$memberAllocation->id] ?? 0);

            $capitalPool = round(
                $memberPool * ((float) $locked->capital_share_percentage / 100),
                2
            );
            $businessPool = round($memberPool - $capitalPool, 2);

            $savingTypeIds = $locked->savingTypes->pluck('id')->map(fn ($id) => (int) $id)->all();
            $capitalBasis = $this->averageDailyCapitalBasis(
                (int) $locked->branch_id,
                $start,
                $end,
                $savingTypeIds
            );
            $businessBasis = $this->businessBasis(
                (int) $locked->branch_id,
                $start,
                $end
            );

            if ($capitalPool > 0 && array_sum($capitalBasis) <= 0) {
                throw ValidationException::withMessages([
                    'shu' => 'Pool Jasa Modal lebih dari nol tetapi tidak ada basis saldo pada jenis simpanan terpilih.',
                ]);
            }

            if ($businessPool > 0 && array_sum($businessBasis) <= 0) {
                throw ValidationException::withMessages([
                    'shu' => 'Pool Jasa Usaha lebih dari nol tetapi tidak ada bunga pinjaman yang dibayar pada periode ini.',
                ]);
            }

            $capitalAwards = $this->allocatePool($capitalBasis, $capitalPool);
            $businessAwards = $this->allocatePool($businessBasis, $businessPool);

            $memberIds = collect(array_keys($capitalBasis))
                ->merge(array_keys($businessBasis))
                ->unique()->values();

            $members = Member::withoutGlobalScope('branch')
                ->where('branch_id', $locked->branch_id)
                ->whereIn('id', $memberIds)
                ->get(['id', 'member_number', 'name'])
                ->keyBy('id');

            $totalCapitalBasis = max(0.0, (float) array_sum($capitalBasis));
            $totalBusinessBasis = max(0.0, (float) array_sum($businessBasis));

            $locked->results()->delete();

            foreach ($memberIds as $memberId) {
                $member = $members->get((int) $memberId);
                if (! $member) {
                    continue;
                }

                $capital = (float) ($capitalBasis[$memberId] ?? 0);
                $business = (float) ($businessBasis[$memberId] ?? 0);
                $capitalShu = (float) ($capitalAwards[$memberId] ?? 0);
                $businessShu = (float) ($businessAwards[$memberId] ?? 0);

                if ($capital <= 0 && $business <= 0 && $capitalShu <= 0 && $businessShu <= 0) {
                    continue;
                }

                $locked->results()->create([
                    'member_id' => $member->id,
                    'member_number_snapshot' => $member->member_number,
                    'member_name_snapshot' => $member->name,
                    'capital_basis' => round($capital, 2),
                    'business_basis' => round($business, 2),
                    'capital_ratio' => $totalCapitalBasis > 0
                        ? round(($capital / $totalCapitalBasis) * 100, 8) : 0,
                    'business_ratio' => $totalBusinessBasis > 0
                        ? round(($business / $totalBusinessBasis) * 100, 8) : 0,
                    'capital_shu' => round($capitalShu, 2),
                    'business_shu' => round($businessShu, 2),
                    'total_shu' => round($capitalShu + $businessShu, 2),
                ]);
            }

            $locked->update([
                'status' => ShuPeriod::STATUS_CALCULATED,
                'calculated_at' => now(),
            ]);

            return $locked->fresh(['branch', 'allocations.account', 'savingTypes']);
        });
    }

    private function validateConfiguration(ShuPeriod $period): void
    {
        $allocations = $period->allocations;

        if ($allocations->isEmpty()) {
            throw ValidationException::withMessages(['allocations' => 'Alokasi SHU belum diisi.']);
        }

        if ($allocations->where('is_member_pool', true)->count() !== 1) {
            throw ValidationException::withMessages([
                'allocations' => 'Harus ada tepat satu alokasi yang ditandai sebagai Bagian Anggota.',
            ]);
        }

        if (abs((float) $allocations->sum('percentage') - 100) > 0.0001) {
            throw ValidationException::withMessages([
                'allocations' => 'Total persentase alokasi SHU harus 100%.',
            ]);
        }

        if (abs(((float) $period->capital_share_percentage + (float) $period->business_share_percentage) - 100) > 0.0001) {
            throw ValidationException::withMessages([
                'member_share' => 'Jasa Modal + Jasa Usaha harus 100%.',
            ]);
        }

        if ((float) $period->capital_share_percentage > 0 && $period->savingTypes->isEmpty()) {
            throw ValidationException::withMessages([
                'saving_type_ids' => 'Pilih minimal satu jenis simpanan sebagai basis Jasa Modal.',
            ]);
        }
    }

    private function averageDailyCapitalBasis(
        int $branchId,
        CarbonImmutable $start,
        CarbonImmutable $end,
        array $savingTypeIds
    ): array {
        if (empty($savingTypeIds)) {
            return [];
        }

        $opening = DB::table('saving_transactions')
            ->where('branch_id', $branchId)
            ->where('status', 'APPROVED')
            ->whereIn('saving_type_id', $savingTypeIds)
            ->whereDate('transaction_date', '<', $start->toDateString())
            ->groupBy('member_id')
            ->selectRaw('member_id, COALESCE(SUM(credit - debit), 0) AS balance')
            ->pluck('balance', 'member_id')
            ->map(fn ($value) => (float) $value)
            ->all();

        $events = DB::table('saving_transactions')
            ->where('branch_id', $branchId)
            ->where('status', 'APPROVED')
            ->whereIn('saving_type_id', $savingTypeIds)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('member_id', 'transaction_date')
            ->orderBy('member_id')
            ->orderBy('transaction_date')
            ->selectRaw('member_id, transaction_date, COALESCE(SUM(credit - debit), 0) AS movement')
            ->get()
            ->groupBy('member_id');

        $memberIds = collect(array_keys($opening))->merge($events->keys())->unique();
        $periodEndExclusive = $end->addDay();
        $totalDays = max(1, $start->diffInDays($periodEndExclusive));
        $result = [];

        foreach ($memberIds as $memberId) {
            $balance = (float) ($opening[$memberId] ?? 0);
            $cursor = $start;
            $weighted = 0.0;

            foreach ($events->get($memberId, collect()) as $event) {
                $eventDate = CarbonImmutable::parse($event->transaction_date)->startOfDay();
                $days = max(0, $cursor->diffInDays($eventDate));
                $weighted += max(0, $balance) * $days;
                $balance += (float) $event->movement;
                $cursor = $eventDate;
            }

            $days = max(0, $cursor->diffInDays($periodEndExclusive));
            $weighted += max(0, $balance) * $days;
            $average = round($weighted / $totalDays, 2);

            if ($average > 0) {
                $result[(int) $memberId] = $average;
            }
        }

        return $result;
    }

    private function businessBasis(
        int $branchId,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        return DB::table('loan_payments as lp')
            ->join('loans as l', 'l.id', '=', 'lp.loan_id')
            ->where('lp.branch_id', $branchId)
            ->where('l.branch_id', $branchId)
            ->whereBetween('lp.payment_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('l.member_id')
            ->selectRaw('l.member_id, COALESCE(SUM(lp.interest_amount), 0) AS interest_paid')
            ->pluck('interest_paid', 'l.member_id')
            ->map(fn ($value) => max(0.0, (float) $value))
            ->filter(fn ($value) => $value > 0)
            ->all();
    }

    private function allocatePool(array $bases, float $pool): array
    {
        $pool = round($pool, 2);
        if ($pool <= 0 || empty($bases)) {
            return [];
        }

        $positive = array_filter($bases, fn ($value) => (float) $value > 0);
        $total = (float) array_sum($positive);
        if ($total <= 0) {
            return [];
        }

        $awards = [];
        $distributed = 0.0;
        $largestMemberId = null;
        $largestBasis = -1.0;

        foreach ($positive as $memberId => $basis) {
            $basis = (float) $basis;
            if ($basis > $largestBasis) {
                $largestBasis = $basis;
                $largestMemberId = (int) $memberId;
            }

            $amount = round($pool * ($basis / $total), 2);
            $awards[(int) $memberId] = $amount;
            $distributed += $amount;
        }

        $difference = round($pool - $distributed, 2);
        if ($largestMemberId !== null && abs($difference) >= 0.01) {
            $awards[$largestMemberId] = round(($awards[$largestMemberId] ?? 0) + $difference, 2);
        }

        return $awards;
    }
}
