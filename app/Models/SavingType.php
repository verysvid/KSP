<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'amount',
        'liability_account_id',
        'is_mandatory',
        'is_withdrawable',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_withdrawable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'liability_account_id'
        );
    }

    public function savingTransactions(): HasMany
    {
        return $this->hasMany(SavingTransaction::class);
    }
}
