<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->foreignId('early_repayment_id')
                ->nullable()
                ->after('loan_id')
                ->constrained('loan_early_repayments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('early_repayment_id');
        });
    }
};
