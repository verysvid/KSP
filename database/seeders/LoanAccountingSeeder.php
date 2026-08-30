<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanAccountingSeeder extends Seeder
{
    public function run(): void
    {
        $cash = Account::firstOrCreate(
            ['code' => '1101'],
            [
                'name' => 'Kas',
                'type' => 'ASSET',
                'is_cash_bank' => true,
                'is_active' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => '1102'],
            [
                'name' => 'Bank',
                'type' => 'ASSET',
                'is_cash_bank' => true,
                'is_active' => true,
            ]
        );

        $receivable = Account::firstOrCreate(
            ['code' => '1201'],
            [
                'name' => 'Piutang Pinjaman',
                'type' => 'ASSET',
                'is_cash_bank' => false,
                'is_active' => true,
            ]
        );

        $interestIncome = Account::firstOrCreate(
            ['code' => '4101'],
            [
                'name' => 'Pendapatan Bunga Pinjaman',
                'type' => 'REVENUE',
                'is_cash_bank' => false,
                'is_active' => true,
            ]
        );

        $penaltyIncome = Account::firstOrCreate(
            ['code' => '4102'],
            [
                'name' => 'Pendapatan Denda Pinjaman',
                'type' => 'REVENUE',
                'is_cash_bank' => false,
                'is_active' => true,
            ]
        );

        LoanType::query()->update([
            'receivable_account_id' => $receivable->id,
            'interest_income_account_id' => $interestIncome->id,
            'penalty_income_account_id' => $penaltyIncome->id,
        ]);
    }
}
