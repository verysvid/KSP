<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkTransaction extends Model
{
    use BelongsToBranch;

    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_COMPLETED = 'COMPLETED';

    protected $fillable = [
        'branch_id', 'batch_no', 'period', 'transaction_date', 'status',
        'member_count', 'saving_total', 'loan_principal_total',
        'loan_interest_total', 'penalty_total', 'grand_total', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'member_count' => 'integer',
            'saving_total' => 'decimal:2',
            'loan_principal_total' => 'decimal:2',
            'loan_interest_total' => 'decimal:2',
            'penalty_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
    public function members(): HasMany { return $this->hasMany(BulkTransactionMember::class); }
    public function items(): HasMany { return $this->hasMany(BulkTransactionItem::class); }
}
