<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LoanPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'loan.view',
            'loan.create',
            'loan.edit',
            'loan.delete',
            'loan.submit',
            'loan.approve',
            'loan.reject',
            'loan.disburse',
            'loan.pay',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'SuperAdmin' => $permissions,

            'Manager' => [
                'loan.view',
                'loan.create',
                'loan.edit',
                'loan.submit',
                'loan.approve',
                'loan.reject',
                'loan.disburse',
                'loan.pay',
            ],

            'Pengurus' => [
                'loan.view',
                'loan.create',
                'loan.edit',
                'loan.submit',
                'loan.pay',
            ],

            'Accounting' => [
                'loan.view',
                'loan.disburse',
                'loan.pay',
            ],

            'Anggota' => [
                'loan.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (!$role) {
                continue;
            }

            $role->givePermissionTo($rolePermissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
