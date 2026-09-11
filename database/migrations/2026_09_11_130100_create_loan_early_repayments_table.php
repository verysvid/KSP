<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_early_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('repayment_no', 60)->unique();
            $table->date('request_date');

            $table->decimal('outstanding_principal', 18, 2);
            $table->decimal('outstanding_interest', 18, 2)->default(0);
            $table->decimal('principal_paid_to_date', 18, 2)->default(0);
            $table->decimal('interest_paid_to_date', 18, 2)->default(0);
            $table->decimal('total_repayment', 18, 2);

            $table->date('installment_start_date')->nullable();
            $table->date('installment_end_date')->nullable();

            $table->string('bank_name', 100);
            $table->string('account_no', 100);
            $table->foreignId('bank_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->string('payment_proof_path');
            $table->text('notes')->nullable();

            $table->enum('status', ['SUBMITTED', 'APPROVED', 'REJECTED'])
                ->default('SUBMITTED');

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['loan_id', 'status']);
            $table->index('request_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_early_repayments');
    }
};
