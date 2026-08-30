<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LoanTypePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Reset Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Loan Type Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'loan-type.view',
            'loan-type.create',
            'loan-type.edit',
            'loan-type.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SuperAdmin
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where('name', 'SuperAdmin')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'loan-type.view',
                'loan-type.create',
                'loan-type.edit',
                'loan-type.delete',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $manager = Role::where('name', 'Manager')->first();

        if ($manager) {
            $manager->givePermissionTo([
				'loan-type.view',
				'loan-type.create',
				'loan-type.edit',
				'loan-type.delete',
            ]);
        }



        /*
        |--------------------------------------------------------------------------
        | Reset Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}