<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->boolean('is_early_repayment')
                ->default(false)
                ->after('is_topup')
                ->index();
        });

        Schema::table('application_settings', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('copyright');
            $table->string('account_no', 100)->nullable()->after('bank_name');
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('account_no')
                ->constrained('accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn(['bank_name', 'account_no']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['is_early_repayment']);
            $table->dropColumn('is_early_repayment');
        });
    }
};
