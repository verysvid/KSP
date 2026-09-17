<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BulkTransaction;
use App\Models\JournalEntry;
use App\Models\SavingTransaction;
use App\Models\ShuPeriod;
use App\Models\YearClosing;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class YearClosingService
{
    public function __construct(
        private readonly IncomeStatementService $incomeStatementService,
        private readonly BalanceSheetService $balanceSheetService,
        private readonly TrialBalanceService $trialBalanceService,
    ) {
    }

    public function preview(int $branchId, int $year): array
    {
        $start = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $end = CarbonImmutable::create($year, 12, 31)->startOfDay();

        $income = $this->incomeStatementService->build(
            $start->toDateString(),
            $end->toDateString(),
            $branchId
        );

        $balance = $this->balanceSheetService->build(
            $end->toDateString(),
            $branchId
        );

        $trial = $this->trialBalanceService->build(
            $start->toDateString(),
            $end->toDateString(),
            $branchId
        );

        $blockers = [];

        if (now()->lte($end->endOfDay())) {
            $blockers[] = 'Tahun buku belum berakhir. Tutup buku baru dapat dilakukan setelah 31 Desember tahun tersebut selesai.';
        }

        $pendingSavings = SavingTransaction::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'PENDING')
            ->count();

        if ($pendingSavings > 0) {
            $blockers[] = "Masih ada {$pendingSavings} transaksi simpanan PENDING pada tahun buku ini.";
        }

        if (class_exists(BulkTransaction::class)) {
            $unfinishedBulk = BulkTransaction::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereYear('transaction_date', $year)
                ->where('status', '!=', BulkTransaction::STATUS_COMPLETED)
                ->count();

            if ($unfinishedBulk > 0) {
                $blockers[] = "Masih ada {$unfinishedBulk} transaksi bulk yang belum COMPLETED pada tahun buku ini.";
            }
        }

        $unbalancedJournals = DB::table('journal_entries as je')
            ->join('journal_entry_lines as jel', 'jel.journal_entry_id', '=', 'je.id')
            ->where('je.branch_id', $branchId)
            ->whereBetween('je.journal_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('je.id')
            ->havingRaw('ABS(SUM(jel.debit) - SUM(jel.credit)) > 0.01')
            ->select('je.id')
            ->get()
            ->count();

        if ($unbalancedJournals > 0) {
            $blockers[] = "Terdapat {$unbalancedJournals} jurnal yang tidak balance.";
        }

        if (! $trial['is_balanced']) {
            $blockers[] = 'Neraca Saldo tidak balance. Selisih: Rp ' . number_format(abs((float) $trial['difference']), 2, ',', '.');
        }

        if (! $balance['is_balanced']) {
            $blockers[] = 'Neraca tidak balance. Selisih: Rp ' . number_format(abs((float) $balance['difference']), 2, ',', '.');
        }

        $existing = YearClosing::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('year', $year)
            ->first();

        return [
            'year' => $year,
            'start_date' => $start,
            'end_date' => $end,
            'income' => $income,
            'balance' => $balance,
            'trial' => $trial,
            'blockers' => $blockers,
            'can_close' => empty($blockers),
            'existing' => $existing,
        ];
    }

    public function close(
        int $branchId,
        int $year,
        int $closingEquityAccountId,
        ?int $userId,
        ?string $note = null
    ): YearClosing {
        return DB::transaction(function () use (
            $branchId,
            $year,
            $closingEquityAccountId,
            $userId,
            $note
        ) {
            $existing = YearClosing::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($existing?->status === YearClosing::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'closing' => 'Tahun buku tersebut sudah CLOSED.',
                ]);
            }

            $equityAccount = Account::query()
                ->whereKey($closingEquityAccountId)
                ->where('type', Account::TYPE_EQUITY)
                ->where('is_postable', true)
                ->where('is_active', true)
                ->first();

            if (! $equityAccount) {
                throw ValidationException::withMessages([
                    'closing_equity_account_id' => 'Akun tujuan hasil usaha harus akun EQUITY yang aktif dan postable.',
                ]);
            }

            $preview = $this->preview($branchId, $year);

            if (! $preview['can_close']) {
                throw ValidationException::withMessages([
                    'closing' => implode(' ', $preview['blockers']),
                ]);
            }

            $income = $preview['income'];
            $balance = $preview['balance'];
            $trial = $preview['trial'];

            if (! $existing) {
                $existing = YearClosing::create([
                    'branch_id' => $branchId,
                    'year' => $year,
                    'start_date' => $preview['start_date']->toDateString(),
                    'end_date' => $preview['end_date']->toDateString(),
                    'status' => 'PROCESSING',
                    'close_count' => 0,
                ]);
            }

            $journal = $this->createClosingJournal(
                $existing,
                $income,
                $equityAccount,
                $userId
            );

            $existing->update([
                'start_date' => $preview['start_date']->toDateString(),
                'end_date' => $preview['end_date']->toDateString(),
                'status' => YearClosing::STATUS_CLOSED,
                'total_revenue' => round((float) $income['total_revenue'], 2),
                'total_expense' => round((float) $income['total_expense'], 2),
                'net_shu' => round((float) $income['net_income'], 2),
                'total_assets' => round((float) $balance['total_assets'], 2),
                'total_liabilities' => round((float) $balance['total_liabilities'], 2),
                'total_equity' => round((float) $balance['total_equity'], 2),
                'balance_difference' => round((float) $balance['difference'], 2),
                'trial_balance_debit' => round((float) $trial['totals']['closing_debit'], 2),
                'trial_balance_credit' => round((float) $trial['totals']['closing_credit'], 2),
                'trial_balance_difference' => round((float) $trial['difference'], 2),
                'income_statement_snapshot' => $this->incomeSnapshot($income),
                'balance_sheet_snapshot' => $this->balanceSnapshot($balance),
                'trial_balance_snapshot' => $this->trialSnapshot($trial),
                'closing_equity_account_id' => $equityAccount->id,
                'journal_entry_id' => $journal->id,
                'close_note' => $note,
                'close_count' => (int) $existing->close_count + 1,
                'closed_at' => now(),
                'closed_by' => $userId,
            ]);

            return $existing->fresh([
                'branch',
                'closedBy',
                'closingEquityAccount',
                'journalEntry',
            ]);
        }, 3);
    }

    public function reopen(
        YearClosing $closing,
        string $reason,
        ?int $userId
    ): YearClosing {
        return DB::transaction(function () use ($closing, $reason, $userId) {
            /** @var YearClosing $locked */
            $locked = YearClosing::withoutGlobalScopes()
                ->whereKey($closing->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== YearClosing::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'closing' => 'Hanya tahun buku CLOSED yang dapat dibuka kembali.',
                ]);
            }

            if (class_exists(ShuPeriod::class)) {
                $lockedShu = ShuPeriod::withoutGlobalScopes()
                    ->where('branch_id', $locked->branch_id)
                    ->where('year', $locked->year)
                    ->whereIn('status', [ShuPeriod::STATUS_FINALIZED, ShuPeriod::STATUS_PAID])
                    ->exists();

                if ($lockedShu) {
                    throw ValidationException::withMessages([
                        'closing' => 'Tahun buku tidak dapat dibuka kembali karena SHU sudah FINALIZED/PAID.',
                    ]);
                }
            }

            $reversalJournal = null;

            if ($locked->journal_entry_id) {
                $sourceJournal = JournalEntry::withoutGlobalScopes()
                    ->with('lines')
                    ->whereKey($locked->journal_entry_id)
                    ->first();

                if ($sourceJournal) {
                    $lines = $sourceJournal->lines->map(fn ($line) => [
                        'account_id' => (int) $line->account_id,
                        'debit' => (float) $line->credit,
                        'credit' => (float) $line->debit,
                        'description' => 'Reversal - ' . ($line->description ?: "Tutup Buku {$locked->year}"),
                    ])->all();

                    $reversalJournal = $this->createSystemJournal(
                        (int) $locked->branch_id,
                        $locked->end_date->toDateString(),
                        "Reversal / Reopen Tutup Buku Tahun {$locked->year}",
                        YearClosing::class,
                        (int) $locked->id,
                        $lines,
                        $userId
                    );
                }
            }

            if (class_exists(ShuPeriod::class)) {
                $shu = ShuPeriod::withoutGlobalScopes()
                    ->where('branch_id', $locked->branch_id)
                    ->where('year', $locked->year)
                    ->first();

                if ($shu) {
                    $shu->results()->delete();
                    $shu->allocations()->update(['amount' => 0]);
                    $shu->update([
                        'year_closing_id' => null,
                        'status' => ShuPeriod::STATUS_DRAFT,
                        'total_revenue' => 0,
                        'total_expense' => 0,
                        'net_shu' => 0,
                        'calculated_at' => null,
                    ]);
                }
            }

            $locked->update([
                'status' => YearClosing::STATUS_REOPENED,
                'reopen_journal_entry_id' => $reversalJournal?->id,
                'reopened_at' => now(),
                'reopened_by' => $userId,
                'reopen_reason' => trim($reason),
            ]);

            return $locked->fresh([
                'branch',
                'closedBy',
                'reopenedBy',
                'journalEntry',
                'reopenJournalEntry',
            ]);
        }, 3);
    }

    public function assertDateOpen(int $branchId, string $date): void
    {
        $closed = YearClosing::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('status', YearClosing::STATUS_CLOSED)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if ($closed) {
            throw ValidationException::withMessages([
                'period' => "Tanggal {$date} berada pada Tahun Buku {$closed->year} yang sudah CLOSED. Reopen tahun buku terlebih dahulu jika koreksi memang diperlukan.",
            ]);
        }
    }

    public function closedFor(int $branchId, int $year): ?YearClosing
    {
        return YearClosing::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('year', $year)
            ->where('status', YearClosing::STATUS_CLOSED)
            ->first();
    }

    private function createClosingJournal(
        YearClosing $closing,
        array $income,
        Account $equityAccount,
        ?int $userId
    ): JournalEntry {
        $lines = [];

        foreach ($income['revenue_rows'] as $row) {
            $amount = round((float) $row->amount, 2);
            if (abs($amount) < 0.01) {
                continue;
            }

            $lines[] = [
                'account_id' => (int) $row->account->id,
                'debit' => $amount > 0 ? $amount : 0,
                'credit' => $amount < 0 ? abs($amount) : 0,
                'description' => "Penutupan pendapatan {$row->account->code} - {$row->account->name}",
            ];
        }

        foreach ($income['expense_rows'] as $row) {
            $amount = round((float) $row->amount, 2);
            if (abs($amount) < 0.01) {
                continue;
            }

            $lines[] = [
                'account_id' => (int) $row->account->id,
                'debit' => $amount < 0 ? abs($amount) : 0,
                'credit' => $amount > 0 ? $amount : 0,
                'description' => "Penutupan beban {$row->account->code} - {$row->account->name}",
            ];
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);
        $difference = round($totalDebit - $totalCredit, 2);

        if (abs($difference) >= 0.01) {
            $lines[] = [
                'account_id' => (int) $equityAccount->id,
                'debit' => $difference < 0 ? abs($difference) : 0,
                'credit' => $difference > 0 ? $difference : 0,
                'description' => $difference > 0
                    ? "Pemindahan SHU Tahun {$closing->year} ke ekuitas"
                    : "Pemindahan rugi Tahun {$closing->year} ke ekuitas",
            ];
        }

        if (empty($lines)) {
            throw ValidationException::withMessages([
                'closing' => 'Tidak ada saldo pendapatan/beban yang dapat ditutup pada tahun buku ini.',
            ]);
        }

        return $this->createSystemJournal(
            (int) $closing->branch_id,
            $closing->end_date->toDateString(),
            "Jurnal Penutup Tahun Buku {$closing->year}",
            YearClosing::class,
            (int) $closing->id,
            $lines,
            $userId
        );
    }

    private function createSystemJournal(
        int $branchId,
        string $journalDate,
        string $description,
        string $referenceType,
        int $referenceId,
        array $lines,
        ?int $createdBy
    ): JournalEntry {
        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new RuntimeException('Jurnal sistem Tutup Buku tidak balance.');
        }

        $journal = JournalEntry::create([
            'branch_id' => $branchId,
            'journal_no' => $this->generateJournalNo(),
            'journal_date' => $journalDate,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'created_by' => $createdBy,
        ]);

        foreach ($lines as $line) {
            $journal->lines()->create([
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? null,
            ]);
        }

        return $journal;
    }

    private function generateJournalNo(): string
    {
        $prefix = 'JR-' . now()->format('Ym');

        $last = JournalEntry::withoutGlobalScopes()
            ->where('journal_no', 'like', $prefix . '-%')
            ->latest('id')
            ->first();

        $sequence = 0;

        if ($last) {
            $parts = explode('-', $last->journal_no);
            $sequence = (int) end($parts);
        }

        return sprintf('%s-%06d', $prefix, $sequence + 1);
    }

    private function incomeSnapshot(array $income): array
    {
        return [
            'total_revenue' => round((float) $income['total_revenue'], 2),
            'total_expense' => round((float) $income['total_expense'], 2),
            'net_income' => round((float) $income['net_income'], 2),
            'is_profit' => (bool) $income['is_profit'],
        ];
    }

    private function balanceSnapshot(array $balance): array
    {
        return [
            'total_assets' => round((float) $balance['total_assets'], 2),
            'total_liabilities' => round((float) $balance['total_liabilities'], 2),
            'total_equity_before_profit' => round((float) $balance['total_equity_before_profit'], 2),
            'current_year_profit' => round((float) $balance['current_year_profit'], 2),
            'total_equity' => round((float) $balance['total_equity'], 2),
            'total_liabilities_and_equity' => round((float) $balance['total_liabilities_and_equity'], 2),
            'difference' => round((float) $balance['difference'], 2),
            'is_balanced' => (bool) $balance['is_balanced'],
        ];
    }

    private function trialSnapshot(array $trial): array
    {
        return [
            'totals' => array_map(fn ($v) => round((float) $v, 2), $trial['totals']),
            'difference' => round((float) $trial['difference'], 2),
            'is_balanced' => (bool) $trial['is_balanced'],
        ];
    }
}
