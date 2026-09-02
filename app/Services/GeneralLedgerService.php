<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneralLedgerService
{
    public function build(
        Account $account,
        string $dateFrom,
        string $dateTo,
        ?int $branchId = null
    ): array {
        $openingQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('jel.account_id', $account->id)
            ->whereDate('je.journal_date', '<', $dateFrom);

        if ($branchId !== null) {
            $openingQuery->where('je.branch_id', $branchId);
        }

        $openingDebit = (float) (clone $openingQuery)->sum('jel.debit');
        $openingCredit = (float) (clone $openingQuery)->sum('jel.credit');

        $openingBalance = $this->signedBalance(
            $account,
            $openingDebit,
            $openingCredit
        );

        $rowsQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->leftJoin('branches as b', 'b.id', '=', 'je.branch_id')
            ->where('jel.account_id', $account->id)
            ->whereDate('je.journal_date', '>=', $dateFrom)
            ->whereDate('je.journal_date', '<=', $dateTo)
            ->orderBy('je.journal_date')
            ->orderBy('je.id')
            ->orderBy('jel.id')
            ->select([
                'jel.id',
                'jel.journal_entry_id',
                'jel.debit',
                'jel.credit',
                'jel.description as line_description',
                'je.journal_no',
                'je.journal_date',
                'je.description as journal_description',
                'je.reference_type',
                'je.reference_id',
                'je.branch_id',
                'b.name as branch_name',
            ]);

        if ($branchId !== null) {
            $rowsQuery->where('je.branch_id', $branchId);
        }

        $rows = $rowsQuery->get();

        $runningBalance = $openingBalance;
        $periodDebit = 0.0;
        $periodCredit = 0.0;

        $rows = $rows->map(function ($row) use (
            $account,
            &$runningBalance,
            &$periodDebit,
            &$periodCredit
        ) {
            $debit = (float) $row->debit;
            $credit = (float) $row->credit;

            $periodDebit += $debit;
            $periodCredit += $credit;

            $runningBalance += $this->movement(
                $account,
                $debit,
                $credit
            );

            $row->debit = $debit;
            $row->credit = $credit;
            $row->balance = $runningBalance;

            return $row;
        });

        return [
            'opening_balance' => $openingBalance,
            'period_debit' => $periodDebit,
            'period_credit' => $periodCredit,
            'closing_balance' => $runningBalance,
            'rows' => $rows,
        ];
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

    private function movement(
        Account $account,
        float $debit,
        float $credit
    ): float {
        return $account->normal_balance === Account::NORMAL_CREDIT
            ? $credit - $debit
            : $debit - $credit;
    }
}
