<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_disbursements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                ->unique()
                ->constrained('loans')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->date('disbursement_date');
            $table->decimal('amount', 18, 2);

            $table->foreignId('cash_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['branch_id', 'disbursement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_disbursements');
    }
};
