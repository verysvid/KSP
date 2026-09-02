<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LedgerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'ledger.view',
            'guard_name' => 'web',
        ]);

        foreach ([
            'SuperAdmin',
            'Manager',
            'Accounting',
        ] as $roleName) {
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                $role->givePermissionTo($permission);
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
