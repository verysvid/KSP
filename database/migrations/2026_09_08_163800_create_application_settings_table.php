<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('system_name', 150);
            $table->string('title_1');
            $table->string('title_2')->nullable();
            $table->string('abbreviation', 30);
            $table->text('description')->nullable();
            $table->string('copyright')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->timestamps();
        });

        DB::table('application_settings')->insert([
            'system_name' => 'Koperasi Simpan Pinjam',
            'title_1' => 'Koperasi Pegawai BPPT Kabupaten Bekasi',
            'title_2' => 'MAHABAH BERSAMA SEJAHTERA',
            'abbreviation' => 'KSP',
            'description' => 'Sistem Informasi Pengelolaan Koperasi Simpan Pinjam',
            'copyright' => '© 2026. All rights reserved.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
