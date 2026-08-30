<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('loan_type_id')->constrained('loan_types')->restrictOnDelete();

            $table->string('loan_no', 50)->unique();
            $table->date('application_date');

            $table->decimal('principal_amount', 18, 2);
            $table->enum('interest_type', ['FLAT', 'EFFECTIVE']);
            $table->decimal('interest_rate', 8, 4);
            $table->unsignedSmallInteger('tenor_months');
            $table->unsignedTinyInteger('due_day');

            $table->enum('status', [
                'DRAFT',
                'SUBMITTED',
                'APPROVED',
                'REJECTED',
                'ACTIVE',
                'PAID_OFF',
                'CANCELLED',
            ])->default('DRAFT');

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('disbursed_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('total_principal', 18, 2)->default(0);
            $table->decimal('total_interest', 18, 2)->default(0);
            $table->decimal('total_installment', 18, 2)->default(0);
            $table->decimal('outstanding_principal', 18, 2)->default(0);
            $table->decimal('outstanding_interest', 18, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['member_id', 'status']);
            $table->index(['loan_type_id', 'status']);
            $table->index('application_date');
            $table->index('due_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
