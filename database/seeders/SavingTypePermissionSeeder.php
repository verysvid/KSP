<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SavingTypePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'saving-type.view',
            'saving-type.create',
            'saving-type.edit',
            'saving-type.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        if ($role = Role::where('name', 'SuperAdmin')->first()) {
            $role->givePermissionTo($permissions);
        }

        if ($role = Role::where('name', 'Manager')->first()) {
            $role->givePermissionTo([
                'saving-type.view',
                'saving-type.create',
                'saving-type.edit',
            ]);
        }
    }
}
