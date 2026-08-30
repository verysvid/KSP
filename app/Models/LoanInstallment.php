<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanInstallment extends Model
{
    protected $fillable = [
        'loan_id',
        'installment_no',
        'due_date',
        'opening_principal',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'installment_amount',
        'ending_principal',
        'principal_paid',
        'interest_paid',
        'penalty_paid',
        'status',
		'is_overdue',
		'days_overdue',
		'overdue_calculated_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'opening_principal' => 'decimal:2',
            'principal_amount' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'ending_principal' => 'decimal:2',
            'principal_paid' => 'decimal:2',
            'interest_paid' => 'decimal:2',
            'penalty_paid' => 'decimal:2',
			'is_overdue' => 'boolean',
			'days_overdue' => 'integer',
			'overdue_calculated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            LoanPayment::class,
            'loan_installment_id'
        );
    }
}
