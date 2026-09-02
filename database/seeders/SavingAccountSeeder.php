<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class SavingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '2101',
                'name' => 'Simpanan Pokok Anggota',
            ],
            [
                'code' => '2102',
                'name' => 'Simpanan Wajib Anggota',
            ],
            [
                'code' => '2103',
                'name' => 'Simpanan Sukarela Anggota',
            ],
        ];

        foreach ($accounts as $index => $data) {
            Account::updateOrCreate(
                [
                    'code' => $data['code'],
                ],
                [
                    'name' => $data['name'],
                    'type' => Account::TYPE_LIABILITY,
                    'normal_balance' => Account::NORMAL_CREDIT,
                    'is_cash_bank' => false,
                    'sort_order' => 2100 + $index + 1,
                    'description' =>
                        'Akun kewajiban simpanan anggota koperasi.',
                    'is_active' => true,
                ]
            );
        }
    }
}
