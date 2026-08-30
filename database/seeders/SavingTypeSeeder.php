<?php

namespace Database\Seeders;

use App\Models\SavingType;
use Illuminate\Database\Seeder;

class SavingTypeSeeder extends Seeder
{
    public function run(): void
    {
        SavingType::updateOrCreate(
            ['code' => 'POKOK'],
            [
                'name' => 'Simpanan Pokok',
                'description' => 'Simpanan pokok anggota.',
                'amount' => 100000,
                'is_mandatory' => true,
                'is_withdrawable' => false,
                'is_active' => true,
            ]
        );

        SavingType::updateOrCreate(
            ['code' => 'WAJIB'],
            [
                'name' => 'Simpanan Wajib',
                'description' => 'Simpanan wajib berkala anggota.',
                'amount' => 50000,
                'is_mandatory' => true,
                'is_withdrawable' => false,
                'is_active' => true,
            ]
        );

        SavingType::updateOrCreate(
            ['code' => 'SUKARELA'],
            [
                'name' => 'Simpanan Sukarela',
                'description' => 'Simpanan sukarela dengan nominal fleksibel.',
                'amount' => null,
                'is_mandatory' => false,
                'is_withdrawable' => true,
                'is_active' => true,
            ]
        );
    }
}
