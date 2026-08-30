<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PAID_OFF = 'PAID_OFF';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const INTEREST_FLAT = 'FLAT';
    public const INTEREST_EFFECTIVE = 'EFFECTIVE';

    protected $fillable = [
        'branch_id',
        'member_id',
        'loan_type_id',
        'loan_no',
        'application_date',
        'principal_amount',
        'interest_type',
        'interest_rate',
        'tenor_months',
        'due_day',
        'status',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'disbursed_at',
        'disbursed_by',
        'total_principal',
        'total_interest',
        'total_installment',
        'outstanding_principal',
        'outstanding_interest',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:4',
            'tenor_months' => 'integer',
            'due_day' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'total_principal' => 'decimal:2',
            'total_interest' => 'decimal:2',
            'total_installment' => 'decimal:2',
            'outstanding_principal' => 'decimal:2',
            'outstanding_interest' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function loanType(): BelongsTo { return $this->belongsTo(LoanType::class); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejectedBy(): BelongsTo { return $this->belongsTo(User::class, 'rejected_by'); }
    public function disbursedBy(): BelongsTo { return $this->belongsTo(User::class, 'disbursed_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }

    public function disbursement(): HasOne
    {
        return $this->hasOne(LoanDisbursement::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class)
            ->orderBy('installment_no');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class)
            ->latest('payment_date')
            ->latest('id');
    }

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isSubmitted(): bool { return $this->status === self::STATUS_SUBMITTED; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
    public function isPaidOff(): bool { return $this->status === self::STATUS_PAID_OFF; }
}
