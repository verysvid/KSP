<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccountPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'account.view',
            'account.create',
            'account.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'SuperAdmin' => $permissions,
            'Manager' => [
                'account.view',
            ],
            'Accounting' => [
                'account.view',
                'account.create',
                'account.edit',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo($rolePermissionNames);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
