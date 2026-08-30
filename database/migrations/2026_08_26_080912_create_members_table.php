<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up(): void
	{
		Schema::create('members', function (Blueprint $table) {
			$table->id();

			$table->foreignId('branch_id')
				->constrained('branches')
				->restrictOnDelete();

			$table->string('member_number', 30)
				->unique();

			$table->string('nik', 30)
				->nullable()
				->unique();

			$table->string('name');

			$table->enum('gender', ['L', 'P'])
				->nullable();

			$table->string('birth_place')
				->nullable();

			$table->date('birth_date')
				->nullable();

			$table->text('address')
				->nullable();

			$table->string('phone', 30)
				->nullable();

			$table->string('email')
				->nullable();

			$table->string('occupation')
				->nullable();

			$table->date('join_date');

			$table->string('member_status', 20)
				->default('ACTIVE');

			$table->string('photo')
				->nullable();

			$table->text('notes')
				->nullable();

			$table->timestamps();

			$table->index([
				'branch_id',
				'member_status'
			]);
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
