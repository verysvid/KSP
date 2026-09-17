<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuMemberResult extends Model
{
    protected $fillable = [
        'shu_period_id', 'member_id', 'member_number_snapshot', 'member_name_snapshot',
        'capital_basis', 'business_basis', 'capital_ratio', 'business_ratio',
        'capital_shu', 'business_shu', 'total_shu',
    ];

    protected function casts(): array
    {
        return [
            'capital_basis' => 'decimal:2',
            'business_basis' => 'decimal:2',
            'capital_ratio' => 'decimal:8',
            'business_ratio' => 'decimal:8',
            'capital_shu' => 'decimal:2',
            'business_shu' => 'decimal:2',
            'total_shu' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ShuPeriod::class, 'shu_period_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
