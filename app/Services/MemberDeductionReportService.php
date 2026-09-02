<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Member;
use App\Models\SavingType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MemberDeductionReportService
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

        $savingAmounts = SavingType::query()
            ->whereIn('code', ['POKOK', 'WAJIB'])
            ->pluck('amount', 'code');

        $members = Member::query()
            ->with('memberType:id,name')
            ->where('branch_id', $branchId)
            ->where('member_status', 'ACTIVE')
            ->whereDate('join_date', '<=', $periodEnd->toDateString())
            ->orderBy('name')
            ->get([
                'id', 'branch_id', 'member_type_id', 'member_number',
                'name', 'amount_saving', 'join_date',
            ]);

        $installments = $this->installments(
            $branchId,
            $members->pluck('id'),
            $periodStart,
            $periodEnd
        );

        $installmentsByMember = $installments->groupBy('loan.member_id');
        $sequence = 0;

        $groups = $members
            ->groupBy(fn (Member $member) => $member->memberType?->name ?: 'Tanpa Jenis Anggota')
            ->sortKeys()
            ->map(function (Collection $groupMembers, string $groupName) use (
                &$sequence,
                $installmentsByMember,
                $savingAmounts,
                $periodStart
            ) {
                $rows = $groupMembers
                    ->sortBy(fn (Member $member) => mb_strtolower($member->name))
                    ->map(function (Member $member) use (
                        &$sequence,
                        $installmentsByMember,
                        $savingAmounts,
                        $periodStart
                    ) {
                        $sequence++;

                        $memberInstallments = $installmentsByMember->get($member->id, collect());
                        $money = $this->summarizeLoanType($memberInstallments, 'PU');
                        $goods = $this->summarizeLoanType($memberInstallments, 'PB');

                        $savingPrincipal = $member->join_date
                            && $member->join_date->format('Y-m') === $periodStart->format('Y-m')
                                ? (float) ($savingAmounts['POKOK'] ?? 0)
                                : 0.0;
                        $savingMandatory = (float) ($savingAmounts['WAJIB'] ?? 0);
                        $savingVoluntary = (float) ($member->amount_saving ?? 0);

                        $moneyTotal = $money['principal'] + $money['interest'];
                        $goodsTotal = $goods['principal'] + $goods['interest'];
                        $loanTotal = $moneyTotal + $goodsTotal;

                        return [
                            'no' => $sequence,
                            'member_number' => $member->member_number,
                            'name' => $member->name,
                            'saving_principal' => $savingPrincipal,
                            'saving_mandatory' => $savingMandatory,
                            'saving_voluntary' => $savingVoluntary,
                            'money_opening' => $money['opening'],
                            'money_principal' => $money['principal'],
                            'money_installment_no' => $money['numbers'],
                            'money_interest' => $money['interest'],
                            'money_ending' => $money['ending'],
                            'money_total' => $moneyTotal,
                            'goods_opening' => $goods['opening'],
                            'goods_principal' => $goods['principal'],
                            'goods_installment_no' => $goods['numbers'],
                            'goods_interest' => $goods['interest'],
                            'goods_ending' => $goods['ending'],
                            'goods_total' => $goodsTotal,
                            'loan_total' => $loanTotal,
                            'all_total' => $savingPrincipal + $savingMandatory + $savingVoluntary + $loanTotal,
                        ];
                    })
                    ->values();

                return [
                    'name' => $groupName,
                    'rows' => $rows,
                    'subtotal' => $this->totals($rows),
                ];
            })
            ->values();

        $allRows = $groups->flatMap(fn (array $group) => $group['rows']);

        return [
            'groups' => $groups,
            'totals' => $this->totals($allRows),
            'memberCount' => $allRows->count(),
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ];
    }

    private function installments(
        int $branchId,
        Collection $memberIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        if ($memberIds->isEmpty()) {
            return collect();
        }

        return LoanInstallment::query()
            ->with([
                'loan:id,branch_id,member_id,loan_type_id,status',
                'loan.loanType:id,code,name',
            ])
            ->whereBetween('due_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->whereHas('loan', function ($query) use ($branchId, $memberIds) {
                $query
                    ->where('branch_id', $branchId)
                    ->whereIn('member_id', $memberIds)
                    ->whereIn('status', [Loan::STATUS_ACTIVE, Loan::STATUS_PAID_OFF])
                    ->whereHas('loanType', fn ($loanType) => $loanType->whereIn('code', ['PU', 'PB']));
            })
            ->orderBy('loan_id')
            ->orderBy('installment_no')
            ->get();
    }

    private function summarizeLoanType(Collection $installments, string $code): array
    {
        $filtered = $installments
            ->filter(fn (LoanInstallment $installment) => $installment->loan?->loanType?->code === $code);

        return [
            'opening' => (float) $filtered->sum('opening_principal'),
            'principal' => (float) $filtered->sum('principal_amount'),
            'numbers' => $filtered->pluck('installment_no')->unique()->sort()->implode(', '),
            'interest' => (float) $filtered->sum('interest_amount'),
            'ending' => (float) $filtered->sum('ending_principal'),
        ];
    }

    private function totals(Collection $rows): array
    {
        return collect(self::MONEY_FIELDS)
            ->mapWithKeys(fn (string $field) => [$field => (float) $rows->sum($field)])
            ->all();
    }
}
