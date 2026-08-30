<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.restore',
            'role.view',
            'role.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach ([
            'SuperAdmin',
            'Manager',
            'Pengurus',
            'Anggota',
            'Accounting',
        ] as $roleName) {
            Role::findOrCreate($roleName);
        }

        $superAdmin = Role::findByName('SuperAdmin');
        $superAdmin->givePermissionTo(Permission::all());

        $manager = Role::findByName('Manager');
        $manager->givePermissionTo([
            'user.view',
            'user.create',
            'user.edit',
        ]);
    }
}
