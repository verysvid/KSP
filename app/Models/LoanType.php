<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'interest_type',
        'interest_rate',
        'min_amount',
        'max_amount',
        'min_tenor',
        'max_tenor',
        'penalty_type',
        'penalty_rate',
        'penalty_amount',
        'receivable_account_id',
        'interest_income_account_id',
        'penalty_income_account_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'interest_rate' => 'decimal:4',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'min_tenor' => 'integer',
            'max_tenor' => 'integer',
            'penalty_rate' => 'decimal:4',
            'penalty_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function receivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'receivable_account_id');
    }

    public function interestIncomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'interest_income_account_id');
    }

    public function penaltyIncomeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'penalty_income_account_id');
    }
}
