<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingTransaction extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'member_id',
        'saving_type_id',
        'transaction_date',
        'period',
        'trx_no',
        'debit',
        'credit',
        'status',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

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
