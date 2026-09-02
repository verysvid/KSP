<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const TYPE_ASSET = 'ASSET';
    public const TYPE_LIABILITY = 'LIABILITY';
    public const TYPE_EQUITY = 'EQUITY';
    public const TYPE_REVENUE = 'REVENUE';
    public const TYPE_EXPENSE = 'EXPENSE';

    public const NORMAL_DEBIT = 'DEBIT';
    public const NORMAL_CREDIT = 'CREDIT';

    protected $fillable = [
        'code','name','type','parent_id','normal_balance','sort_order','description',
        'is_cash_bank','is_postable','is_active',
    ];

    protected $casts = [
        'is_cash_bank' => 'boolean',
        'is_postable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('code');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePostable(Builder $query): Builder
    {
        return $query->where('is_postable', true);
    }

    public function scopeHeaders(Builder $query): Builder
    {
        return $query->where('is_postable', false);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ASSET => 'Aset',
            self::TYPE_LIABILITY => 'Liabilitas',
            self::TYPE_EQUITY => 'Ekuitas',
            self::TYPE_REVENUE => 'Pendapatan',
            self::TYPE_EXPENSE => 'Beban',
            default => $this->type,
        };
    }

    public function getNormalBalanceLabelAttribute(): string
    {
        return $this->normal_balance === self::NORMAL_CREDIT ? 'Kredit' : 'Debit';
    }
}
