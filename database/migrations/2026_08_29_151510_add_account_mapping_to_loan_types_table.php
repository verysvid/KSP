<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_types', function (Blueprint $table) {
            $table->foreignId('receivable_account_id')
                ->nullable()
                ->after('penalty_amount')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('interest_income_account_id')
                ->nullable()
                ->after('receivable_account_id')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('penalty_income_account_id')
                ->nullable()
                ->after('interest_income_account_id')
                ->constrained('accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penalty_income_account_id');
            $table->dropConstrainedForeignId('interest_income_account_id');
            $table->dropConstrainedForeignId('receivable_account_id');
        });
    }
};
