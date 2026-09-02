<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JournalService
{
    public function create(
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

        if ($totalDebit !== $totalCredit) {
            throw new RuntimeException('Jurnal tidak balance.');
        }

        return DB::transaction(function () use (
            $branchId,
            $journalDate,
            $description,
            $referenceType,
            $referenceId,
            $lines,
            $createdBy
        ) {
            $journal = JournalEntry::create([
                'branch_id' => $branchId,
				'journal_no' => $this->generateJournalNo($branchId),
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
        });
    }

	protected function generateJournalNo(int $branchId): string
	{
		$prefix = 'JR-' . now()->format('Ym');

		$last = JournalEntry::withoutGlobalScope('branch')
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

}
