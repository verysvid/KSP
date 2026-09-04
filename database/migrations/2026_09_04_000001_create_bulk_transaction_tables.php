<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('batch_no', 50)->unique();
            $table->string('period', 7)->index();
            $table->date('transaction_date');
            $table->string('status', 20)->default('PROCESSING');
            $table->unsignedInteger('member_count')->default(0);
            $table->decimal('saving_total', 18, 2)->default(0);
            $table->decimal('loan_principal_total', 18, 2)->default(0);
            $table->decimal('loan_interest_total', 18, 2)->default(0);
            $table->decimal('penalty_total', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'period']);
            $table->index(['status', 'transaction_date']);
        });

        Schema::create('bulk_transaction_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->string('period', 7);
            $table->decimal('saving_principal', 18, 2)->default(0);
            $table->decimal('saving_mandatory', 18, 2)->default(0);
            $table->decimal('saving_voluntary', 18, 2)->default(0);
            $table->decimal('money_principal', 18, 2)->default(0);
            $table->decimal('money_interest', 18, 2)->default(0);
            $table->decimal('goods_principal', 18, 2)->default(0);
            $table->decimal('goods_interest', 18, 2)->default(0);
            $table->decimal('penalty_total', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'period', 'member_id'], 'bulk_member_period_unique');
        });

        Schema::create('bulk_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bulk_transaction_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->string('item_type', 30);
            $table->string('category', 30);
            $table->foreignId('saving_transaction_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('loan_payment_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('principal_amount', 18, 2)->default(0);
            $table->decimal('interest_amount', 18, 2)->default(0);
            $table->decimal('penalty_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->timestamps();
            $table->index(['item_type', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_transaction_items');
        Schema::dropIfExists('bulk_transaction_members');
        Schema::dropIfExists('bulk_transactions');
    }
};
