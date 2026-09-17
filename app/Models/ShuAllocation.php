<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuAllocation extends Model
{
    protected $fillable = [
        'shu_period_id', 'name', 'percentage', 'amount',
        'is_member_pool', 'account_id', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:4',
            'amount' => 'decimal:2',
            'is_member_pool' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ShuPeriod::class, 'shu_period_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
