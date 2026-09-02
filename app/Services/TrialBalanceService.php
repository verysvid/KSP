<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function build(
        string $dateFrom,
        string $dateTo,
        ?int $branchId = null
    ): array {
        $accounts = Account::query()
            ->where('is_postable', true)
            ->with([
                'parent:id,code,name,parent_id,sort_order',
                'parent.parent:id,code,name,parent_id,sort_order',
            ])
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'type',
                'parent_id',
                'normal_balance',
                'sort_order',
                'is_active',
                'is_postable',
            ]);

        $opening = $this->totalsBefore($dateFrom, $branchId);
        $period = $this->totalsBetween($dateFrom, $dateTo, $branchId);

        $rows = $accounts->map(function (Account $account) use ($opening, $period) {
            $openingDebit = (float) ($opening[$account->id]->debit ?? 0);
            $openingCredit = (float) ($opening[$account->id]->credit ?? 0);

            $periodDebit = (float) ($period[$account->id]->debit ?? 0);
            $periodCredit = (float) ($period[$account->id]->credit ?? 0);

            $openingSigned = $this->signedBalance(
                $account,
                $openingDebit,
                $openingCredit
            );

            $movementSigned = $this->signedBalance(
                $account,
                $periodDebit,
                $periodCredit
            );

            $closingSigned = $openingSigned + $movementSigned;

            [$openingBalanceDebit, $openingBalanceCredit] =
                $this->toDebitCreditColumns($account, $openingSigned);

            [$closingBalanceDebit, $closingBalanceCredit] =
                $this->toDebitCreditColumns($account, $closingSigned);

            return (object) [
                'account' => $account,
                'opening_debit' => $openingBalanceDebit,
                'opening_credit' => $openingBalanceCredit,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_debit' => $closingBalanceDebit,
                'closing_credit' => $closingBalanceCredit,
                'parent_code' => $account->parent?->code,
                'parent_name' => $account->parent?->name,
                'root_code' => $account->parent?->parent?->code
                    ?? $account->parent?->code
                    ?? $account->code,
                'root_name' => $account->parent?->parent?->name
                    ?? $account->parent?->name
                    ?? $account->name,
            ];
        });

        $rows = $rows
            ->sortBy(function ($row) {
                $account = $row->account;

                $rootSort = $account->parent?->parent?->sort_order
                    ?? $account->parent?->sort_order
                    ?? $account->sort_order;

                $parentSort = $account->parent?->sort_order
                    ?? $account->sort_order;

                return sprintf(
                    '%010d-%010d-%010d-%s',
                    (int) $rootSort,
                    (int) $parentSort,
                    (int) $account->sort_order,
                    $account->code
                );
            })
            ->values();

        $groups = $this->groupRows($rows);

        $totals = [
            'opening_debit' => (float) $rows->sum('opening_debit'),
            'opening_credit' => (float) $rows->sum('opening_credit'),
            'period_debit' => (float) $rows->sum('period_debit'),
            'period_credit' => (float) $rows->sum('period_credit'),
            'closing_debit' => (float) $rows->sum('closing_debit'),
            'closing_credit' => (float) $rows->sum('closing_credit'),
        ];

        $difference = round(
            $totals['closing_debit'] - $totals['closing_credit'],
            2
        );

        return [
            'rows' => $rows,
            'groups' => $groups,
            'totals' => $totals,
            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    private function totalsBefore(
        string $dateFrom,
        ?int $branchId
    ): Collection {
        $query = DB::table('journal_entry_lines as jel')
            ->join(
                'journal_entries as je',
                'je.id',
                '=',
                'jel.journal_entry_id'
            )
            ->whereDate('je.journal_date', '<', $dateFrom)
            ->groupBy('jel.account_id')
            ->selectRaw(
                'jel.account_id, ' .
                'COALESCE(SUM(jel.debit), 0) AS debit, ' .
                'COALESCE(SUM(jel.credit), 0) AS credit'
            );

        if ($branchId !== null) {
            $query->where('je.branch_id', $branchId);
        }

        return $query
            ->get()
            ->keyBy('account_id');
    }

    private function totalsBetween(
        string $dateFrom,
        string $dateTo,
        ?int $branchId
    ): Collection {
        $query = DB::table('journal_entry_lines as jel')
            ->join(
                'journal_entries as je',
                'je.id',
                '=',
                'jel.journal_entry_id'
            )
            ->whereDate('je.journal_date', '>=', $dateFrom)
            ->whereDate('je.journal_date', '<=', $dateTo)
            ->groupBy('jel.account_id')
            ->selectRaw(
                'jel.account_id, ' .
                'COALESCE(SUM(jel.debit), 0) AS debit, ' .
                'COALESCE(SUM(jel.credit), 0) AS credit'
            );

        if ($branchId !== null) {
            $query->where('je.branch_id', $branchId);
        }

        return $query
            ->get()
            ->keyBy('account_id');
    }

    private function signedBalance(
        Account $account,
        float $debit,
        float $credit
    ): float {
        return $account->normal_balance === Account::NORMAL_CREDIT
            ? $credit - $debit
            : $debit - $credit;
    }

    private function toDebitCreditColumns(
        Account $account,
        float $signedBalance
    ): array {
        if ($signedBalance == 0.0) {
            return [0.0, 0.0];
        }

        if ($account->normal_balance === Account::NORMAL_CREDIT) {
            return $signedBalance >= 0
                ? [0.0, $signedBalance]
                : [abs($signedBalance), 0.0];
        }

        return $signedBalance >= 0
            ? [$signedBalance, 0.0]
            : [0.0, abs($signedBalance)];
    }

    private function groupRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn ($row) => $row->root_code . '|' . $row->root_name)
            ->map(function (Collection $rootRows, string $rootKey) {
                [$rootCode, $rootName] = explode('|', $rootKey, 2);

                $children = $rootRows
                    ->groupBy(function ($row) {
                        $code = $row->parent_code ?? $row->root_code;
                        $name = $row->parent_name ?? $row->root_name;

                        return $code . '|' . $name;
                    })
                    ->map(function (Collection $childRows, string $childKey) {
                        [$childCode, $childName] = explode('|', $childKey, 2);

                        return (object) [
                            'code' => $childCode,
                            'name' => $childName,
                            'rows' => $childRows->values(),
                            'totals' => [
                                'opening_debit' => (float) $childRows->sum('opening_debit'),
                                'opening_credit' => (float) $childRows->sum('opening_credit'),
                                'period_debit' => (float) $childRows->sum('period_debit'),
                                'period_credit' => (float) $childRows->sum('period_credit'),
                                'closing_debit' => (float) $childRows->sum('closing_debit'),
                                'closing_credit' => (float) $childRows->sum('closing_credit'),
                            ],
                        ];
                    })
                    ->values();

                return (object) [
                    'code' => $rootCode,
                    'name' => $rootName,
                    'children' => $children,
                    'totals' => [
                        'opening_debit' => (float) $rootRows->sum('opening_debit'),
                        'opening_credit' => (float) $rootRows->sum('opening_credit'),
                        'period_debit' => (float) $rootRows->sum('period_debit'),
                        'period_credit' => (float) $rootRows->sum('period_credit'),
                        'closing_debit' => (float) $rootRows->sum('closing_debit'),
                        'closing_credit' => (float) $rootRows->sum('closing_credit'),
                    ],
                ];
            })
            ->values();
    }
}
