<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('year_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('CLOSED');

            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->decimal('total_expense', 18, 2)->default(0);
            $table->decimal('net_shu', 18, 2)->default(0);

            $table->decimal('total_assets', 18, 2)->default(0);
            $table->decimal('total_liabilities', 18, 2)->default(0);
            $table->decimal('total_equity', 18, 2)->default(0);
            $table->decimal('balance_difference', 18, 2)->default(0);

            $table->decimal('trial_balance_debit', 18, 2)->default(0);
            $table->decimal('trial_balance_credit', 18, 2)->default(0);
            $table->decimal('trial_balance_difference', 18, 2)->default(0);

            $table->json('income_statement_snapshot')->nullable();
            $table->json('balance_sheet_snapshot')->nullable();
            $table->json('trial_balance_snapshot')->nullable();

            $table->foreignId('closing_equity_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('reopen_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();

            $table->text('close_note')->nullable();
            $table->unsignedSmallInteger('close_count')->default(1);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'year']);
            $table->index(['status', 'year']);
            $table->index(['branch_id', 'status', 'start_date', 'end_date'], 'year_closings_period_lock_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('year_closings');
    }
};
