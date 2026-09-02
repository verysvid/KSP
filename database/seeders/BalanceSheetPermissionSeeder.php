<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BalanceSheetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'balance-sheet.view',
            'guard_name' => 'web',
        ]);

        foreach ([
            'SuperAdmin',
            'Super Admin',
            'Manager',
            'Manager Cabang',
            'Accounting',
        ] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
