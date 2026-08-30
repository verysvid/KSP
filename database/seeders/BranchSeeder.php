<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['code' => 'PST'],
            [
                'name' => 'Kantor Pusat',
                'address' => 'Alamat Kantor Pusat',
                'phone' => '021-0000000',
                'email' => 'pusat@koperasi.test',
                'manager_name' => 'Manager Pusat',
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'BKS'],
            [
                'name' => 'Cabang Bekasi',
                'address' => 'Alamat Cabang Bekasi',
                'phone' => '021-0000000',
                'email' => 'bekasi@koperasi.test',
                'manager_name' => 'Manager Bekasi',
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'JKT'],
            [
                'name' => 'Cabang Jakarta',
                'address' => 'Alamat Cabang Jakarta',
                'phone' => '021-1111111',
                'email' => 'jakarta@koperasi.test',
                'manager_name' => 'Manager Jakarta',
                'is_active' => true,
            ]
        );
    }
}