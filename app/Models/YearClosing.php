<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YearClosing extends Model
{
    use BelongsToBranch;

    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_REOPENED = 'REOPENED';

    protected $fillable = [
        'branch_id',
        'year',
        'start_date',
        'end_date',
        'status',
        'total_revenue',
        'total_expense',
        'net_shu',
        'total_assets',
        'total_liabilities',
        'total_equity',
        'balance_difference',
        'trial_balance_debit',
        'trial_balance_credit',
        'trial_balance_difference',
        'income_statement_snapshot',
        'balance_sheet_snapshot',
        'trial_balance_snapshot',
        'closing_equity_account_id',
        'journal_entry_id',
        'reopen_journal_entry_id',
        'close_note',
        'close_count',
        'closed_at',
        'closed_by',
        'reopened_at',
        'reopened_by',
        'reopen_reason',
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
            'total_assets' => 'decimal:2',
            'total_liabilities' => 'decimal:2',
            'total_equity' => 'decimal:2',
            'balance_difference' => 'decimal:2',
            'trial_balance_debit' => 'decimal:2',
            'trial_balance_credit' => 'decimal:2',
            'trial_balance_difference' => 'decimal:2',
            'income_statement_snapshot' => 'array',
            'balance_sheet_snapshot' => 'array',
            'trial_balance_snapshot' => 'array',
            'close_count' => 'integer',
            'closed_at' => 'datetime',
            'reopened_at' => 'datetime',
        ];
    }

    public function closingEquityAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'closing_equity_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function reopenJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reopen_journal_entry_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isReopened(): bool
    {
        return $this->status === self::STATUS_REOPENED;
    }
}
