<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_types', function (Blueprint $table) {
            $table->foreignId('liability_account_id')
                ->nullable()
                ->after('amount')
                ->constrained('accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saving_types', function (Blueprint $table) {
            $table->dropForeign(['liability_account_id']);
            $table->dropColumn('liability_account_id');
        });
    }
};
