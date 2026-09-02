<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Member;
use App\Models\SavingTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MemberSavingReportController extends Controller
{
    /**
     * Sesuaikan bila status approved transaksi simpanan di project Anda berbeda.
     */
    private const APPROVED_STATUS = 'APPROVED';

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasAnyRole(['Anggota', 'Manager', 'Pengurus', 'SuperAdmin']),
            403
        );

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = Carbon::parse(
            $validated['start_date'] ?? now()->startOfMonth()->toDateString()
        )->startOfDay();

        $endDate = Carbon::parse(
            $validated['end_date'] ?? now()->endOfMonth()->toDateString()
        )->endOfDay();

        if ($endDate->lt($startDate)) {
            return back()
                ->withInput()
                ->withErrors([
                    'end_date' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.',
                ]);
        }

        $branches = collect();
        $members = collect();
        $selectedMember = null;
        $selectedBranchId = null;

        if ($user->hasRole('Anggota')) {
            $selectedMember = $user->member()
                ->with('branch:id,code,name')
                ->first();

            abort_unless(
                $selectedMember,
                403,
                'User Anggota belum terhubung dengan data anggota.'
            );

            $selectedBranchId = (int) $selectedMember->branch_id;
        } elseif ($user->hasRole('Manager', 'Pengurus')) {
            abort_unless(
                $user->branch_id,
                403,
                'User Manager belum memiliki cabang.'
            );

            $selectedBranchId = (int) $user->branch_id;

            $members = Member::query()
                ->where('branch_id', $selectedBranchId)
                ->where('member_status', 'ACTIVE')
                ->orderBy('name')
                ->get([
                    'id',
                    'branch_id',
                    'member_number',
                    'name',
                ]);

            if (!empty($validated['member_id'])) {
                $selectedMember = Member::query()
                    ->with('branch:id,code,name')
                    ->whereKey((int) $validated['member_id'])
                    ->where('branch_id', $selectedBranchId)
                    ->first();

                abort_unless($selectedMember, 403);
            }
        } else {
            // SuperAdmin
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);

            if (!empty($validated['branch_id'])) {
                $selectedBranchId = (int) $validated['branch_id'];

                $members = Member::query()
                    ->where('branch_id', $selectedBranchId)
                    ->where('member_status', 'ACTIVE')
                    ->orderBy('name')
                    ->get([
                        'id',
                        'branch_id',
                        'member_number',
                        'name',
                    ]);
            }

            if (!empty($validated['member_id'])) {
                abort_unless(
                    $selectedBranchId,
                    422,
                    'Pilih cabang terlebih dahulu sebelum memilih anggota.'
                );

                $selectedMember = Member::query()
                    ->with('branch:id,code,name')
                    ->whereKey((int) $validated['member_id'])
                    ->where('branch_id', $selectedBranchId)
                    ->first();

                abort_unless($selectedMember, 403);
            }
        }

        $transactions = collect();
        $openingBalance = 0.0;
        $closingBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        if ($selectedMember) {
            $openingBalance = $this->calculateOpeningBalance(
                $selectedMember,
                $startDate
            );

            $transactions = $this->getPeriodTransactions(
                $selectedMember,
                $startDate,
                $endDate,
                $openingBalance
            );

            $totalDebit = (float) $transactions->sum('debit');
            $totalCredit = (float) $transactions->sum('credit');
            $closingBalance = $openingBalance + $totalCredit - $totalDebit;
        }

        return view('reports.savings.index', compact(
            'branches',
            'members',
            'selectedMember',
            'selectedBranchId',
            'startDate',
            'endDate',
            'transactions',
            'openingBalance',
            'closingBalance',
            'totalDebit',
            'totalCredit'
        ));
    }

    private function calculateOpeningBalance(
        Member $member,
        Carbon $startDate
    ): float {
        $totals = SavingTransaction::query()
            ->where('member_id', $member->id)
            ->where('status', self::APPROVED_STATUS)
            ->whereDate('transaction_date', '<', $startDate->toDateString())
            ->selectRaw(
                'COALESCE(SUM(credit), 0) AS total_credit, COALESCE(SUM(debit), 0) AS total_debit'
            )
            ->first();

        return (float) $totals->total_credit - (float) $totals->total_debit;
    }

    private function getPeriodTransactions(
        Member $member,
        Carbon $startDate,
        Carbon $endDate,
        float $openingBalance
    ): Collection {
        $runningBalance = $openingBalance;

        return SavingTransaction::query()
            ->with([
                'savingType:id,code,name',
            ])
            ->where('member_id', $member->id)
            ->where('status', self::APPROVED_STATUS)
            ->whereBetween('transaction_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->map(function (SavingTransaction $transaction) use (&$runningBalance) {
                $debit = (float) $transaction->debit;
                $credit = (float) $transaction->credit;

                $runningBalance += $credit - $debit;

                $transaction->setAttribute('running_balance', $runningBalance);

                return $transaction;
            });
    }
}
