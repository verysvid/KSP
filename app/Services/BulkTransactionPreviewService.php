<?php

namespace App\Services;

use App\Models\BulkTransactionMember;
use App\Models\Loan;
use App\Models\Member;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BulkTransactionPreviewService
{
    private const MONEY_FIELDS = [
        'saving_principal', 'saving_mandatory', 'saving_voluntary',
        'money_opening', 'money_principal', 'money_interest', 'money_ending', 'money_total',
        'goods_opening', 'goods_principal', 'goods_interest', 'goods_ending', 'goods_total',
        'loan_total', 'all_total',
    ];

    public function generate(int $branchId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $period = $periodStart->format('Y-m');

        $types = SavingType::query()
            ->whereIn('code', ['POKOK', 'WAJIB', 'MANASUKA', 'SUKARELA'])
            ->get()
            ->keyBy(fn (SavingType $type) => strtoupper($type->code));

        $members = Member::query()
            ->with('memberType:id,name')
            ->where('branch_id', $branchId)
            ->where('member_status', 'ACTIVE')
            ->whereDate('join_date', '<=', $periodEnd->toDateString())
            ->orderBy('name')
            ->get(['id', 'branch_id', 'member_type_id', 'member_number', 'name', 'amount_saving', 'join_date']);

        $memberIds = $members->pluck('id');
        $processed = BulkTransactionMember::query()
            ->with('bulkTransaction:id,batch_no')
            ->where('branch_id', $branchId)
            ->where('period', $period)
            ->whereIn('member_id', $memberIds)
            ->get()
            ->keyBy('member_id');

        $existingSavings = SavingTransaction::withoutGlobalScopes()
            ->whereIn('member_id', $memberIds)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where(function ($query) use ($period, $types) {
                $query->where('period', $period);
                if ($pokok = $types->get('POKOK')) {
                    $query->orWhere('saving_type_id', $pokok->id);
                }
            })
            ->get(['member_id', 'saving_type_id', 'period'])
            ->groupBy('member_id');

        $loans = Loan::withoutGlobalScopes()
            ->with([
                'loanType:id,code,name',
                'installments' => fn ($query) => $query
                    ->where('status', '!=', 'PAID')
                    ->whereDate('due_date', '<=', $periodEnd->toDateString())
                    ->orderBy('due_date')
                    ->orderBy('installment_no'),
            ])
            ->where('branch_id', $branchId)
            ->whereIn('member_id', $memberIds)
            ->where('status', Loan::STATUS_ACTIVE)
            ->whereHas('loanType', fn ($query) => $query->whereIn('code', ['PU', 'PB']))
            ->get()
            ->groupBy('member_id');

        $sequence = 0;
        $groups = $members
            ->groupBy(fn (Member $member) => $member->memberType?->name ?: 'Tanpa Jenis Anggota')
            ->sortKeys()
            ->map(function (Collection $groupMembers, string $groupName) use (
                &$sequence, $processed, $existingSavings, $loans, $types, $periodStart
            ) {
                $rows = $groupMembers->sortBy(fn (Member $member) => mb_strtolower($member->name))
                    ->map(function (Member $member) use (
                        &$sequence, $processed, $existingSavings, $loans, $types, $periodStart
                    ) {
                        $sequence++;
                        $memberSavings = $existingSavings->get($member->id, collect());
                        $hasSaving = fn (?SavingType $type, ?string $period = null) => $type
                            ? $memberSavings->contains(fn ($trx) =>
                                (int) $trx->saving_type_id === (int) $type->id
                                && ($period === null || $trx->period === $period))
                            : false;

                        $pokok = $types->get('POKOK');
                        $wajib = $types->get('WAJIB');
                        $voluntaryType = $types->get('MANASUKA') ?? $types->get('SUKARELA');
                        $period = $periodStart->format('Y-m');

                        $savingPrincipal = $member->join_date
                            && $member->join_date->format('Y-m') === $period
                            && !$hasSaving($pokok)
                            ? (float) ($pokok?->amount ?? 0) : 0.0;
                        $savingMandatory = !$hasSaving($wajib, $period)
                            ? (float) ($wajib?->amount ?? 0) : 0.0;
                        $savingVoluntary = !$hasSaving($voluntaryType, $period)
                            ? (float) ($member->amount_saving ?? 0) : 0.0;

                        $memberLoans = $loans->get($member->id, collect());
                        $moneyInstallments = collect();
                        $goodsInstallments = collect();
                        $blockedReason = null;

                        foreach ($memberLoans as $loan) {
                            $installment = $loan->installments->first();
                            if (!$installment) continue;
                            if ($installment->status === 'PARTIAL') {
                                $blockedReason = "Angsuran parsial pada {$loan->loan_no}";
                            }
                            if (strtoupper((string) $loan->loanType?->code) === 'PU') {
                                $moneyInstallments->push($installment);
                            } elseif (strtoupper((string) $loan->loanType?->code) === 'PB') {
                                $goodsInstallments->push($installment);
                            }
                        }

                        $money = $this->summarize($moneyInstallments);
                        $goods = $this->summarize($goodsInstallments);
                        $moneyTotal = $money['principal'] + $money['interest'];
                        $goodsTotal = $goods['principal'] + $goods['interest'];
                        $loanTotal = $moneyTotal + $goodsTotal;
                        $allTotal = $savingPrincipal + $savingMandatory + $savingVoluntary + $loanTotal;
                        $processedRow = $processed->get($member->id);

                        return [
                            'no' => $sequence, 'member_id' => $member->id,
                            'member_number' => $member->member_number, 'name' => $member->name,
                            'saving_principal' => $savingPrincipal,
                            'saving_mandatory' => $savingMandatory,
                            'saving_voluntary' => $savingVoluntary,
                            'money_opening' => $money['opening'], 'money_principal' => $money['principal'],
                            'money_installment_no' => $money['numbers'], 'money_interest' => $money['interest'],
                            'money_ending' => $money['ending'], 'money_total' => $moneyTotal,
                            'goods_opening' => $goods['opening'], 'goods_principal' => $goods['principal'],
                            'goods_installment_no' => $goods['numbers'], 'goods_interest' => $goods['interest'],
                            'goods_ending' => $goods['ending'], 'goods_total' => $goodsTotal,
                            'loan_total' => $loanTotal, 'all_total' => $allTotal,
                            'processed_batch' => $processedRow?->bulkTransaction?->batch_no,
                            'blocked_reason' => $blockedReason,
                            'selectable' => !$processedRow && !$blockedReason && $allTotal > 0,
                        ];
                    })->values();

                return ['name' => $groupName, 'rows' => $rows, 'subtotal' => $this->totals($rows)];
            })->values();

        $allRows = $groups->flatMap(fn (array $group) => $group['rows']);

        return [
            'groups' => $groups, 'totals' => $this->totals($allRows),
            'memberCount' => $allRows->count(),
            'selectableCount' => $allRows->where('selectable', true)->count(),
            'periodStart' => $periodStart, 'periodEnd' => $periodEnd,
        ];
    }

    private function summarize(Collection $installments): array
    {
        return [
            'opening' => (float) $installments->sum('opening_principal'),
            'principal' => (float) $installments->sum('principal_amount'),
            'interest' => (float) $installments->sum('interest_amount'),
            'ending' => (float) $installments->sum('ending_principal'),
            'numbers' => $installments->pluck('installment_no')->filter()->unique()->sort()->implode(', '),
        ];
    }

    private function totals(Collection $rows): array
    {
        return collect(self::MONEY_FIELDS)
            ->mapWithKeys(fn (string $field) => [$field => (float) $rows->sum($field)])
            ->all();
    }
}
