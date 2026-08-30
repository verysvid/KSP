<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('installment_no');
            $table->date('due_date');

            $table->decimal('opening_principal', 18, 2);
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('interest_amount', 18, 2);
            $table->decimal('penalty_amount', 18, 2)->default(0);
            $table->decimal('installment_amount', 18, 2);
            $table->decimal('ending_principal', 18, 2);

            $table->decimal('principal_paid', 18, 2)->default(0);
            $table->decimal('interest_paid', 18, 2)->default(0);
            $table->decimal('penalty_paid', 18, 2)->default(0);

            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID'])
                ->default('UNPAID');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['loan_id', 'installment_no']);
            $table->index(['loan_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
