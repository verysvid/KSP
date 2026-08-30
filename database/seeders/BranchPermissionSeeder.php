<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BranchPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'branch.view',
            'branch.create',
            'branch.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        if ($role = Role::where('name', 'SuperAdmin')->first()) {
            $role->givePermissionTo($permissions);
        }

        if ($role = Role::where('name', 'Manager')->first()) {
            $role->givePermissionTo([
                'branch.view',
            ]);
        }
    }
}
