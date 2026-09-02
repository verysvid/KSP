<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    public function build(string $dateFrom, string $dateTo, ?int $branchId = null): array
    {
        $accounts = Account::query()
            ->where('is_postable', true)
            ->whereIn('type', [Account::TYPE_REVENUE, Account::TYPE_EXPENSE])
            ->with([
                'parent:id,code,name,parent_id,sort_order',
                'parent.parent:id,code,name,parent_id,sort_order',
            ])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get([
                'id','code','name','type','parent_id','normal_balance',
                'sort_order','is_active','is_postable',
            ]);

        $period = $this->periodTotals($dateFrom, $dateTo, $branchId);

        $rows = $accounts->map(function (Account $account) use ($period) {
            $debit = (float) ($period[$account->id]->debit ?? 0);
            $credit = (float) ($period[$account->id]->credit ?? 0);

            $amount = $account->type === Account::TYPE_REVENUE
                ? $credit - $debit
                : $debit - $credit;

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

        $revenueRows = $rows->where('account.type', Account::TYPE_REVENUE)->values();
        $expenseRows = $rows->where('account.type', Account::TYPE_EXPENSE)->values();

        $totalRevenue = (float) $revenueRows->sum('amount');
        $totalExpense = (float) $expenseRows->sum('amount');
        $netIncome = $totalRevenue - $totalExpense;

        return [
            'revenue_rows' => $revenueRows,
            'expense_rows' => $expenseRows,
            'revenue_groups' => $this->groupRows($revenueRows),
            'expense_groups' => $this->groupRows($expenseRows),
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
            'is_profit' => $netIncome >= 0,
        ];
    }

    private function periodTotals(string $dateFrom, string $dateTo, ?int $branchId): Collection
    {
        $query = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereDate('je.journal_date', '>=', $dateFrom)
            ->whereDate('je.journal_date', '<=', $dateTo)
            ->groupBy('jel.account_id')
            ->selectRaw(
                'jel.account_id, COALESCE(SUM(jel.debit), 0) AS debit, COALESCE(SUM(jel.credit), 0) AS credit'
            );

        if ($branchId !== null) {
            $query->where('je.branch_id', $branchId);
        }

        return $query->get()->keyBy('account_id');
    }

    private function groupRows(Collection $rows): Collection
    {
        return $rows
            ->sortBy(function ($row) {
                $account = $row->account;
                $rootSort = $account->parent?->parent?->sort_order
                    ?? $account->parent?->sort_order
                    ?? $account->sort_order;
                $parentSort = $account->parent?->sort_order ?? $account->sort_order;

                return sprintf(
                    '%010d-%010d-%010d-%s',
                    (int) $rootSort,
                    (int) $parentSort,
                    (int) $account->sort_order,
                    $account->code
                );
            })
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
                            'total' => (float) $childRows->sum('amount'),
                        ];
                    })->values();

                return (object) [
                    'code' => $rootCode,
                    'name' => $rootName,
                    'children' => $children,
                    'total' => (float) $rootRows->sum('amount'),
                ];
            })->values();
    }
}
