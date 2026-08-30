<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SavingTransactionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'saving-transaction.view',
            'saving-transaction.create',
            'saving-transaction.approve',
            'saving-transaction.reject',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        if ($role = Role::where('name', 'SuperAdmin')->first()) {
            $role->givePermissionTo($permissions);
        }

        if ($role = Role::where('name', 'Manager')->first()) {
            $role->givePermissionTo($permissions);
        }

        if ($role = Role::where('name', 'Pengurus')->first()) {
            $role->givePermissionTo([
                'saving-transaction.view',
                'saving-transaction.create',
            ]);
        }
    }
}
