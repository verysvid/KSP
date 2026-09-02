<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->foreignId('cash_account_id')
                ->nullable()
                ->after('saving_type_id')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('journal_entry_id')
                ->nullable()
                ->after('remarks')
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->index(['branch_id', 'cash_account_id']);
        });
    }

    public function down(): void
    {
        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'cash_account_id']);

            $table->dropForeign(['journal_entry_id']);
            $table->dropForeign(['cash_account_id']);

            $table->dropColumn([
                'journal_entry_id',
                'cash_account_id',
            ]);
        });
    }
};
