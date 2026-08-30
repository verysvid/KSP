<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->enum('interest_type', [
                'FLAT',
                'EFFECTIVE',
            ])->default('FLAT');

            $table->decimal('interest_rate', 8, 4)->default(0);

            $table->decimal('min_amount', 18, 2)->nullable();
            $table->decimal('max_amount', 18, 2)->nullable();

            $table->unsignedSmallInteger('min_tenor')->default(1);
            $table->unsignedSmallInteger('max_tenor')->nullable();

            $table->enum('penalty_type', [
                'NONE',
                'FIXED',
                'PERCENTAGE',
            ])->default('NONE');

            $table->decimal('penalty_rate', 8, 4)->nullable();
            $table->decimal('penalty_amount', 18, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
            $table->index('interest_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};