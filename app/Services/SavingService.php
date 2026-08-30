<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavingService
{
    public function createTransaction(array $data): SavingTransaction
    {
        return DB::transaction(function () use ($data) {
            $member = Member::query()->findOrFail($data['member_id']);
            $savingType = SavingType::query()->findOrFail($data['saving_type_id']);

            $transactionType = strtoupper($data['transaction_type']);
            $amount = (float) $data['amount'];
            $transactionDate = Carbon::parse($data['transaction_date']);

            $this->validateBusinessRules(
                member: $member,
                savingType: $savingType,
                transactionType: $transactionType,
                amount: $amount,
                transactionDate: $transactionDate
            );

            $transaction = SavingTransaction::create([
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'saving_type_id' => $savingType->id,
                'transaction_date' => $transactionDate->toDateString(),
                'period' => $transactionDate->format('Y-m'),
                'trx_no' => $this->generateTransactionNumber(
                    branchId: $member->branch_id,
                    transactionDate: $transactionDate
                ),
                'debit' => $transactionType === 'PENARIKAN' ? $amount : 0,
                'credit' => $transactionType === 'SETORAN' ? $amount : 0,
                'status' => 'PENDING',
                'remarks' => $data['remarks'] ?? null,
            ]);

            $this->audit(
                'CREATE',
                $transaction,
                'Membuat transaksi simpanan ' . $transaction->trx_no,
                [],
                $transaction->only([
                    'branch_id',
                    'member_id',
                    'saving_type_id',
                    'transaction_date',
                    'period',
                    'trx_no',
                    'debit',
                    'credit',
                    'status',
                    'remarks',
                ])
            );

            return $transaction;
        });
    }

    public function approve(SavingTransaction $transaction): SavingTransaction
    {
        if ($transaction->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'transaction' => 'Hanya transaksi PENDING yang dapat di-approve.',
            ]);
        }

        return DB::transaction(function () use ($transaction) {
            $this->revalidateBeforeApproval($transaction);

            $oldValues = [
                'status' => $transaction->status,
                'approved_by' => $transaction->approved_by,
                'approved_at' => $transaction->approved_at,
            ];

            $transaction->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->audit(
                'APPROVE',
                $transaction,
                'Menyetujui transaksi simpanan ' . $transaction->trx_no,
                $oldValues,
                [
                    'status' => $transaction->status,
                    'approved_by' => $transaction->approved_by,
                    'approved_at' => $transaction->approved_at,
                ]
            );

            return $transaction;
        });
    }

    public function reject(
        SavingTransaction $transaction,
        ?string $remarks = null
    ): SavingTransaction {
        if ($transaction->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'transaction' => 'Hanya transaksi PENDING yang dapat ditolak.',
            ]);
        }

        return DB::transaction(function () use ($transaction, $remarks) {
            $oldValues = [
                'status' => $transaction->status,
                'remarks' => $transaction->remarks,
            ];

            $transaction->update([
                'status' => 'REJECTED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $remarks
                    ? trim(($transaction->remarks ? $transaction->remarks . PHP_EOL : '') . 'Alasan reject: ' . $remarks)
                    : $transaction->remarks,
            ]);

            $this->audit(
                'REJECT',
                $transaction,
                'Menolak transaksi simpanan ' . $transaction->trx_no,
                $oldValues,
                [
                    'status' => $transaction->status,
                    'remarks' => $transaction->remarks,
                ]
            );

            return $transaction;
        });
    }

    public function getApprovedBalance(
        int $memberId,
        int $savingTypeId
    ): float {
        $credit = SavingTransaction::query()
            ->where('member_id', $memberId)
            ->where('saving_type_id', $savingTypeId)
            ->where('status', 'APPROVED')
            ->sum('credit');

        $debit = SavingTransaction::query()
            ->where('member_id', $memberId)
            ->where('saving_type_id', $savingTypeId)
            ->where('status', 'APPROVED')
            ->sum('debit');

        return (float) $credit - (float) $debit;
    }

    private function validateBusinessRules(
        Member $member,
        SavingType $savingType,
        string $transactionType,
        float $amount,
        Carbon $transactionDate
    ): void {
        if ($member->member_status !== 'ACTIVE') {
            throw ValidationException::withMessages([
                'member_id' => 'Anggota tidak aktif.',
            ]);
        }

        if (! $savingType->is_active) {
            throw ValidationException::withMessages([
                'saving_type_id' => 'Jenis simpanan tidak aktif.',
            ]);
        }

        if ($transactionType === 'PENARIKAN') {
            if (! $savingType->is_withdrawable) {
                throw ValidationException::withMessages([
                    'transaction_type' => 'Jenis simpanan ini tidak dapat ditarik.',
                ]);
            }

            $balance = $this->getApprovedBalance(
                $member->id,
                $savingType->id
            );

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo simpanan tidak mencukupi.',
                ]);
            }
        }

        $code = strtoupper($savingType->code);

        if ($code === 'POKOK' && $transactionType === 'SETORAN') {
            $alreadyExists = SavingTransaction::query()
                ->where('member_id', $member->id)
                ->where('saving_type_id', $savingType->id)
                ->whereIn('status', ['PENDING', 'APPROVED'])
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages([
                    'saving_type_id' => 'Simpanan Pokok hanya boleh disetor satu kali.',
                ]);
            }
        }

        if (
            $code === 'WAJIB'
            && $transactionType === 'SETORAN'
            && $savingType->amount !== null
            && (float) $savingType->amount !== $amount
        ) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal Simpanan Wajib harus Rp '
                    . number_format((float) $savingType->amount, 0, ',', '.')
                    . '.',
            ]);
        }
    }

    private function revalidateBeforeApproval(
        SavingTransaction $transaction
    ): void {
        $transaction->loadMissing(['member', 'savingType']);

        if ($transaction->debit > 0) {
            $balance = $this->getApprovedBalance(
                $transaction->member_id,
                $transaction->saving_type_id
            );

            if ((float) $transaction->debit > $balance) {
                throw ValidationException::withMessages([
                    'transaction' => 'Saldo tidak mencukupi untuk menyetujui penarikan ini.',
                ]);
            }
        }

        if (
            strtoupper($transaction->savingType->code) === 'POKOK'
            && $transaction->credit > 0
        ) {
            $approvedExists = SavingTransaction::query()
                ->where('member_id', $transaction->member_id)
                ->where('saving_type_id', $transaction->saving_type_id)
                ->where('status', 'APPROVED')
                ->whereKeyNot($transaction->id)
                ->exists();

            if ($approvedExists) {
                throw ValidationException::withMessages([
                    'transaction' => 'Anggota sudah memiliki Simpanan Pokok yang disetujui.',
                ]);
            }
        }
    }

    private function generateTransactionNumber(
        int $branchId,
        Carbon $transactionDate
    ): string {
        $date = $transactionDate->format('Ymd');

        $last = SavingTransaction::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereDate('transaction_date', $transactionDate->toDateString())
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $sequence = 1;

        if ($last && preg_match('/-(\d{6})$/', $last->trx_no, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf(
            'SV-%02d-%s-%06d',
            $branchId,
            $date,
            $sequence
        );
    }

    private function audit(
        string $action,
        SavingTransaction $transaction,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->log(
            action: $action,
            model: $transaction,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}
