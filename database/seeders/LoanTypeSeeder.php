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
                'code' => 'PU',
                'name' => 'Pinjaman Uang',
                'description' => 'Pinjaman untuk kebutuhan modal usaha atau kegiatan produktif.',
                'interest_type' => 'EFFECTIVE',
                'interest_rate' => 3.0000,
                'min_amount' => 1000000,
                'max_amount' => 50000000,
                'min_tenor' => 10,
                'max_tenor' => 20,
                'penalty_type' => 'NONE',
                'penalty_rate' => null,
                'penalty_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PB',
                'name' => 'Pinjaman Barang',
                'description' => 'Pinjaman untuk kebutuhan konsumtif anggota.',
                'interest_type' => 'EFFECTIVE',
                'interest_rate' => 1.0000,
                'min_amount' => 500000,
                'max_amount' => 25000000,
                'min_tenor' => 10,
                'max_tenor' => 20,
                'penalty_type' => 'NONE',
                'penalty_rate' => null,
                'penalty_amount' => null,
                'is_active' => true,
            ],
        ];

        foreach ($loanTypes as $loanType) {
            LoanType::updateOrCreate(['code' => $loanType['code']], $loanType);
        }
    }
}
