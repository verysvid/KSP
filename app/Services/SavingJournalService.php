<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SavingTransaction;
use RuntimeException;

class SavingJournalService
{
    public function __construct(
        protected JournalService $journalService
    ) {
    }

    public function post(
        SavingTransaction $transaction,
        int $cashAccountId,
        ?int $userId = null
    ) {
        $transaction->loadMissing([
            'savingType.liabilityAccount',
        ]);

        if ($transaction->journal_entry_id) {
            throw new RuntimeException(
                'Transaksi simpanan ini sudah memiliki jurnal.'
            );
        }

        $cashAccount = Account::query()
            ->whereKey($cashAccountId)
            ->where('type', Account::TYPE_ASSET)
            ->where('is_cash_bank', true)
            ->where('is_active', true)
            ->first();

        if (!$cashAccount) {
            throw new RuntimeException(
                'Akun Kas/Bank tidak valid atau tidak aktif.'
            );
        }

        $liabilityAccount =
            $transaction->savingType?->liabilityAccount;

        if (!$liabilityAccount) {
            throw new RuntimeException(
                'Jenis simpanan belum memiliki mapping akun kewajiban.'
            );
        }

        if (
            $liabilityAccount->type !== Account::TYPE_LIABILITY
            || !$liabilityAccount->is_active
        ) {
            throw new RuntimeException(
                'Akun kewajiban simpanan tidak valid atau tidak aktif.'
            );
        }

        $debit = round((float) $transaction->debit, 2);
        $credit = round((float) $transaction->credit, 2);

        if ($credit > 0 && $debit <= 0) {
            $movement = 'SETORAN';
            $amount = $credit;

            $lines = [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' =>
                        'Penerimaan kas/bank transaksi simpanan '
                        . $transaction->trx_no,
                ],
                [
                    'account_id' => $liabilityAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' =>
                        'Kewajiban simpanan anggota '
                        . $transaction->trx_no,
                ],
            ];
        } elseif ($debit > 0 && $credit <= 0) {
            $movement = 'PENARIKAN';
            $amount = $debit;

            $lines = [
                [
                    'account_id' => $liabilityAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' =>
                        'Pengurangan kewajiban simpanan anggota '
                        . $transaction->trx_no,
                ],
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' =>
                        'Pengeluaran kas/bank transaksi simpanan '
                        . $transaction->trx_no,
                ],
            ];
        } else {
            throw new RuntimeException(
                'Transaksi simpanan harus memiliki tepat satu nilai debit atau credit.'
            );
        }

        if ($amount <= 0) {
            throw new RuntimeException(
                'Nominal transaksi simpanan harus lebih besar dari nol.'
            );
        }

        $journal = $this->journalService->create(
            branchId: (int) $transaction->branch_id,
            journalDate: $transaction->transaction_date->format('Y-m-d'),
            description:
                $movement
                . ' '
                . ($transaction->savingType?->name ?? 'Simpanan')
                . ' - '
                . $transaction->trx_no,
            referenceType: SavingTransaction::class,
            referenceId: (int) $transaction->id,
            lines: $lines,
            createdBy: $userId
        );

        $transaction->forceFill([
            'cash_account_id' => $cashAccount->id,
            'journal_entry_id' => $journal->id,
        ])->save();

        return $journal;
    }
}
