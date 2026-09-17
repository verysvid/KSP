<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shu_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('year_closing_id')->nullable()->constrained('year_closings')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('DRAFT');

            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->decimal('total_expense', 18, 2)->default(0);
            $table->decimal('net_shu', 18, 2)->default(0);

            $table->decimal('capital_share_percentage', 7, 4)->default(40);
            $table->decimal('business_share_percentage', 7, 4)->default(60);

            $table->boolean('post_journal')->default(false);
            $table->foreignId('source_equity_account_id')->nullable()
                ->constrained('accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()
                ->constrained('journal_entries')->nullOnDelete();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('paid_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('payment_note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'year']);
            $table->index(['status', 'year']);
        });

        Schema::create('shu_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_period_id')->constrained('shu_periods')->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('percentage', 7, 4)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->boolean('is_member_pool')->default(false);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['shu_period_id', 'sort_order']);
        });

        Schema::create('shu_period_saving_type', function (Blueprint $table) {
            $table->foreignId('shu_period_id')->constrained('shu_periods')->cascadeOnDelete();
            $table->foreignId('saving_type_id')->constrained('saving_types')->restrictOnDelete();
            $table->primary(['shu_period_id', 'saving_type_id']);
        });

        Schema::create('shu_member_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_period_id')->constrained('shu_periods')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->string('member_number_snapshot', 80)->nullable();
            $table->string('member_name_snapshot', 160);
            $table->decimal('capital_basis', 18, 2)->default(0);
            $table->decimal('business_basis', 18, 2)->default(0);
            $table->decimal('capital_ratio', 12, 8)->default(0);
            $table->decimal('business_ratio', 12, 8)->default(0);
            $table->decimal('capital_shu', 18, 2)->default(0);
            $table->decimal('business_shu', 18, 2)->default(0);
            $table->decimal('total_shu', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['shu_period_id', 'member_id']);
            $table->index(['member_id', 'shu_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_member_results');
        Schema::dropIfExists('shu_period_saving_type');
        Schema::dropIfExists('shu_allocations');
        Schema::dropIfExists('shu_periods');
    }
};
