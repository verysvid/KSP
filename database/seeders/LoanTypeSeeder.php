<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    public function run(): void
    {
        $loanTypes = [
            [
                'code' => 'PROD',
                'name' => 'Pinjaman Produktif',
                'description' => 'Pinjaman untuk kebutuhan modal usaha atau kegiatan produktif.',
                'interest_type' => 'EFFECTIVE',
                'interest_rate' => 1.0000,
                'min_amount' => 1000000,
                'max_amount' => 50000000,
                'min_tenor' => 3,
                'max_tenor' => 36,
                'penalty_type' => 'NONE',
                'penalty_rate' => null,
                'penalty_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'CONS',
                'name' => 'Pinjaman Konsumtif',
                'description' => 'Pinjaman untuk kebutuhan konsumtif anggota.',
                'interest_type' => 'FLAT',
                'interest_rate' => 1.2500,
                'min_amount' => 500000,
                'max_amount' => 25000000,
                'min_tenor' => 3,
                'max_tenor' => 24,
                'penalty_type' => 'NONE',
                'penalty_rate' => null,
                'penalty_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'EMER',
                'name' => 'Pinjaman Darurat',
                'description' => 'Pinjaman untuk kebutuhan mendesak atau keadaan darurat anggota.',
                'interest_type' => 'FLAT',
                'interest_rate' => 0.7500,
                'min_amount' => 500000,
                'max_amount' => 10000000,
                'min_tenor' => 1,
                'max_tenor' => 12,
                'penalty_type' => 'NONE',
                'penalty_rate' => null,
                'penalty_amount' => null,
                'is_active' => true,
            ],
        ];

        foreach ($loanTypes as $loanType) {
            LoanType::updateOrCreate(
                [
                    'code' => $loanType['code'],
                ],
                $loanType
            );
        }
    }
}