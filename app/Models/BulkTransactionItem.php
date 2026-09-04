<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkTransactionItem extends Model
{
    public const TYPE_SAVING = 'SAVING';
    public const TYPE_LOAN_PAYMENT = 'LOAN_PAYMENT';

    protected $fillable = [
        'bulk_transaction_id', 'bulk_transaction_member_id', 'member_id',
        'item_type', 'category', 'saving_transaction_id', 'loan_payment_id',
        'principal_amount', 'interest_amount', 'penalty_amount', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2', 'interest_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2', 'total_amount' => 'decimal:2',
        ];
    }

    public function bulkTransaction(): BelongsTo { return $this->belongsTo(BulkTransaction::class); }
    public function bulkMember(): BelongsTo { return $this->belongsTo(BulkTransactionMember::class, 'bulk_transaction_member_id'); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function savingTransaction(): BelongsTo { return $this->belongsTo(SavingTransaction::class); }
    public function loanPayment(): BelongsTo { return $this->belongsTo(LoanPayment::class); }
}
