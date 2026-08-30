<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('saving_type_id')
                ->constrained('saving_types')
                ->restrictOnDelete();

            $table->date('transaction_date');
            $table->string('period', 7);
            $table->string('trx_no', 40)->unique();

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->string('status', 20)->default('PENDING');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'transaction_date']);
            $table->index(['member_id', 'saving_type_id']);
            $table->index(['status', 'transaction_date']);
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_transactions');
    }
};
