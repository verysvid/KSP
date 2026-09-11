<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanEarlyRepayment extends Model
{
    use BelongsToBranch;

    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'loan_id',
        'branch_id',
        'repayment_no',
        'request_date',
        'outstanding_principal',
        'outstanding_interest',
        'principal_paid_to_date',
        'interest_paid_to_date',
        'total_repayment',
        'installment_start_date',
        'installment_end_date',
        'bank_name',
        'account_no',
        'bank_account_id',
        'payment_proof_path',
        'notes',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'outstanding_principal' => 'decimal:2',
            'outstanding_interest' => 'decimal:2',
            'principal_paid_to_date' => 'decimal:2',
            'interest_paid_to_date' => 'decimal:2',
            'total_repayment' => 'decimal:2',
            'installment_start_date' => 'date',
            'installment_end_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
