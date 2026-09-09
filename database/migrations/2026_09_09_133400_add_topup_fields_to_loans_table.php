<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->boolean('is_topup')->default(false)->after('notes')->index();
            $table->foreignId('topup_from_loan_id')
                ->nullable()
                ->after('is_topup')
                ->constrained('loans')
                ->nullOnDelete();
            $table->string('old_loan_no', 100)->nullable()->after('topup_from_loan_id')->index();
            $table->decimal('topup_amount', 18, 2)->nullable()->after('old_loan_no');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['topup_from_loan_id']);
            $table->dropColumn([
                'is_topup',
                'topup_from_loan_id',
                'old_loan_no',
                'topup_amount',
            ]);
        });
    }
};
