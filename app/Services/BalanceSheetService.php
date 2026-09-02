<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    public function build(
        string $asOfDate,
        ?int $branchId = null
    ): array {
        $accounts = Account::query()
            ->where('is_postable', true)
            ->whereIn('type', [
                Account::TYPE_ASSET,
                Account::TYPE_LIABILITY,
                Account::TYPE_EQUITY,
            ])
            ->with([
                'parent:id,code,name,parent_id,sort_order',
                'parent.parent:id,code,name,parent_id,sort_order',
            ])
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

        $totals = $this->accountTotalsUntil(
            $asOfDate,
            $branchId
        );

        $rows = $accounts->map(function (Account $account) use ($totals) {
            $debit = (float) ($totals[$account->id]->debit ?? 0);
            $credit = (float) ($totals[$account->id]->credit ?? 0);

            $amount = match ($account->type) {
                Account::TYPE_ASSET => $debit - $credit,
                Account::TYPE_LIABILITY,
                Account::TYPE_EQUITY => $credit - $debit,
                default => 0.0,
            };

            return (object) [
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'amount' => $amount,
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

        $assetRows = $rows
            ->where('account.type', Account::TYPE_ASSET)
            ->values();

        $liabilityRows = $rows
            ->where('account.type', Account::TYPE_LIABILITY)
            ->values();

        $equityRows = $rows
            ->where('account.type', Account::TYPE_EQUITY)
            ->values();

        $currentYearProfit = $this->currentYearProfit(
            $asOfDate,
            $branchId
        );

        $totalAssets = (float) $assetRows->sum('amount');
        $totalLiabilities = (float) $liabilityRows->sum('amount');
        $totalEquityBeforeProfit = (float) $equityRows->sum('amount');

        $totalEquity = $totalEquityBeforeProfit + $currentYearProfit;

        $totalLiabilitiesAndEquity =
            $totalLiabilities + $totalEquity;

        $difference = round(
            $totalAssets - $totalLiabilitiesAndEquity,
            2
        );

        return [
            'asset_rows' => $assetRows,
            'liability_rows' => $liabilityRows,
            'equity_rows' => $equityRows,

            'asset_groups' => $this->groupRows($assetRows),
            'liability_groups' => $this->groupRows($liabilityRows),
            'equity_groups' => $this->groupRows($equityRows),

            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity_before_profit' => $totalEquityBeforeProfit,
            'current_year_profit' => $currentYearProfit,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,

            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    private function accountTotalsUntil(
        string $asOfDate,
        ?int $branchId
    ): Collection {
        $query = DB::table('journal_entry_lines as jel')
            ->join(
                'journal_entries as je',
                'je.id',
                '=',
                'jel.journal_entry_id'
            )
            ->whereDate('je.journal_date', '<=', $asOfDate)
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

    private function currentYearProfit(
        string $asOfDate,
        ?int $branchId
    ): float {
        $yearStart = date(
            'Y-01-01',
            strtotime($asOfDate)
        );

        $query = DB::table('journal_entry_lines as jel')
            ->join(
                'journal_entries as je',
                'je.id',
                '=',
                'jel.journal_entry_id'
            )
            ->join(
                'accounts as a',
                'a.id',
                '=',
                'jel.account_id'
            )
            ->where('a.is_postable', true)
            ->whereIn('a.type', [
                Account::TYPE_REVENUE,
                Account::TYPE_EXPENSE,
            ])
            ->whereDate('je.journal_date', '>=', $yearStart)
            ->whereDate('je.journal_date', '<=', $asOfDate);

        if ($branchId !== null) {
            $query->where('je.branch_id', $branchId);
        }

        $result = $query
            ->selectRaw(
                "COALESCE(SUM(CASE " .
                "WHEN a.type = ? THEN jel.credit - jel.debit " .
                "WHEN a.type = ? THEN -(jel.debit - jel.credit) " .
                "ELSE 0 END), 0) AS net_income",
                [
                    Account::TYPE_REVENUE,
                    Account::TYPE_EXPENSE,
                ]
            )
            ->value('net_income');

        return (float) $result;
    }

    private function groupRows(Collection $rows): Collection
    {
        return $rows
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
            ->groupBy(
                fn ($row) =>
                    $row->root_code . '|' . $row->root_name
            )
            ->map(function (
                Collection $rootRows,
                string $rootKey
            ) {
                [$rootCode, $rootName] =
                    explode('|', $rootKey, 2);

                $children = $rootRows
                    ->groupBy(function ($row) {
                        $code =
                            $row->parent_code
                            ?? $row->root_code;

                        $name =
                            $row->parent_name
                            ?? $row->root_name;

                        return $code . '|' . $name;
                    })
                    ->map(function (
                        Collection $childRows,
                        string $childKey
                    ) {
                        [$childCode, $childName] =
                            explode('|', $childKey, 2);

                        return (object) [
                            'code' => $childCode,
                            'name' => $childName,
                            'rows' => $childRows->values(),
                            'total' =>
                                (float) $childRows->sum('amount'),
                        ];
                    })
                    ->values();

                return (object) [
                    'code' => $rootCode,
                    'name' => $rootName,
                    'children' => $children,
                    'total' =>
                        (float) $rootRows->sum('amount'),
                ];
            })
            ->values();
    }
}
