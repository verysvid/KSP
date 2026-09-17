<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuPeriod extends Model
{
    use BelongsToBranch;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_CALCULATED = 'CALCULATED';
    public const STATUS_FINALIZED = 'FINALIZED';
    public const STATUS_PAID = 'PAID';

    protected $fillable = [
        'branch_id', 'year_closing_id', 'year', 'start_date', 'end_date', 'status',
        'total_revenue', 'total_expense', 'net_shu',
        'capital_share_percentage', 'business_share_percentage',
        'post_journal', 'source_equity_account_id', 'journal_entry_id',
        'calculated_at', 'finalized_at', 'finalized_by',
        'paid_date', 'paid_at', 'paid_by', 'payment_note',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_revenue' => 'decimal:2',
            'total_expense' => 'decimal:2',
            'net_shu' => 'decimal:2',
            'capital_share_percentage' => 'decimal:4',
            'business_share_percentage' => 'decimal:4',
            'post_journal' => 'boolean',
            'calculated_at' => 'datetime',
            'finalized_at' => 'datetime',
            'paid_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function yearClosing(): BelongsTo
    {
        return $this->belongsTo(YearClosing::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ShuAllocation::class)->orderBy('sort_order')->orderBy('id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ShuMemberResult::class)->orderByDesc('total_shu');
    }

    public function savingTypes(): BelongsToMany
    {
        return $this->belongsToMany(SavingType::class, 'shu_period_saving_type');
    }

    public function sourceEquityAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_equity_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function memberAllocation(): ?ShuAllocation
    {
        if ($this->relationLoaded('allocations')) {
            return $this->allocations->firstWhere('is_member_pool', true);
        }

        return $this->allocations()->where('is_member_pool', true)->first();
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_FINALIZED, self::STATUS_PAID], true);
    }
}
