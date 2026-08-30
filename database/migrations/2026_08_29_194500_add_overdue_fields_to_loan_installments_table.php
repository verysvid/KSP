<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->boolean('is_overdue')->default(false)->after('status');
            $table->unsignedInteger('days_overdue')->default(0)->after('is_overdue');
            $table->timestamp('overdue_calculated_at')->nullable()->after('days_overdue');
            $table->index(['is_overdue','due_date']);
        });
    }
    public function down(): void {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->dropIndex(['is_overdue','due_date']);
            $table->dropColumn(['is_overdue','days_overdue','overdue_calculated_at']);
        });
    }
};
