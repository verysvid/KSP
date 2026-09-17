<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Services\YearClosingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingTransaction extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'member_id',
        'saving_type_id',
        'cash_account_id',
        'transaction_date',
        'period',
        'trx_no',
        'debit',
        'credit',
        'status',
        'approved_by',
        'approved_at',
        'remarks',
        'journal_entry_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (SavingTransaction $transaction) {
            if ($transaction->branch_id && $transaction->transaction_date) {
                app(YearClosingService::class)->assertDateOpen(
                    (int) $transaction->branch_id,
                    $transaction->transaction_date instanceof \DateTimeInterface
                        ? $transaction->transaction_date->format('Y-m-d')
                        : (string) $transaction->transaction_date
                );
            }
        });

        static::updating(function (SavingTransaction $transaction) {
            if (
                ($transaction->isDirty('transaction_date') || $transaction->isDirty('branch_id'))
                && $transaction->branch_id
                && $transaction->transaction_date
            ) {
                app(YearClosingService::class)->assertDateOpen(
                    (int) $transaction->branch_id,
                    $transaction->transaction_date instanceof \DateTimeInterface
                        ? $transaction->transaction_date->format('Y-m-d')
                        : (string) $transaction->transaction_date
                );
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function savingType(): BelongsTo
    {
        return $this->belongsTo(SavingType::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'cash_account_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_entry_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getAmountAttribute(): float
    {
        return (float) ($this->credit > 0 ? $this->credit : $this->debit);
    }

    public function getMutationTypeAttribute(): string
    {
        return $this->credit > 0 ? 'SETORAN' : 'PENARIKAN';
    }
}
