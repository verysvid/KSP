<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkTransactionMember extends Model
{
    protected $fillable = [
        'bulk_transaction_id', 'branch_id', 'member_id', 'period',
        'saving_principal', 'saving_mandatory', 'saving_voluntary',
        'money_principal', 'money_interest', 'goods_principal',
        'goods_interest', 'penalty_total', 'grand_total',
    ];

    protected function casts(): array
    {
        return [
            'saving_principal' => 'decimal:2', 'saving_mandatory' => 'decimal:2',
            'saving_voluntary' => 'decimal:2', 'money_principal' => 'decimal:2',
            'money_interest' => 'decimal:2', 'goods_principal' => 'decimal:2',
            'goods_interest' => 'decimal:2', 'penalty_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function bulkTransaction(): BelongsTo { return $this->belongsTo(BulkTransaction::class); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function items(): HasMany { return $this->hasMany(BulkTransactionItem::class); }
}
