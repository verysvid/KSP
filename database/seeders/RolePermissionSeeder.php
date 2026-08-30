<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Dashboard
            'dashboard.view',

            // Anggota
            'member.view',
            'member.create',
            'member.edit',
            'member.delete',

            // Simpanan
            'saving.view',
            'saving.create',
            'saving.edit',
            'saving.delete',

            // Pinjaman
            'loan.view',
            'loan.create',
            'loan.edit',
            'loan.delete',
            'loan.approve',

            // Angsuran
            'installment.view',
            'installment.create',
            'installment.edit',

            // Accounting
            'accounting.view',
            'journal.view',
            'journal.create',
            'journal.edit',

            // Reports
            'report.view',

            // SHU
            'shu.view',
            'shu.process',

            // Closing
            'closing.view',
            'closing.process',

            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Branch
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $pengurus = Role::firstOrCreate([
            'name' => 'Pengurus',
            'guard_name' => 'web',
        ]);

        $accounting = Role::firstOrCreate([
            'name' => 'Accounting',
            'guard_name' => 'web',
        ]);

        $anggota = Role::firstOrCreate([
            'name' => 'Anggota',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $manager->syncPermissions([
            'dashboard.view',

            'member.view',
            'member.create',
            'member.edit',

            'saving.view',
            'saving.create',
            'saving.edit',

            'loan.view',
            'loan.create',
            'loan.edit',
            'loan.approve',

            'installment.view',
            'installment.create',

            'report.view',

            'shu.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pengurus
        |--------------------------------------------------------------------------
        */

        $pengurus->syncPermissions([
            'dashboard.view',

            'member.view',
            'member.create',
            'member.edit',

            'saving.view',
            'saving.create',

            'loan.view',
            'loan.create',

            'installment.view',
            'installment.create',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        $accounting->syncPermissions([
            'dashboard.view',

            'saving.view',
            'loan.view',
            'installment.view',

            'accounting.view',

            'journal.view',
            'journal.create',
            'journal.edit',

            'report.view',

            'shu.view',
            'shu.process',

            'closing.view',
            'closing.process',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Anggota
        |--------------------------------------------------------------------------
        */

        $anggota->syncPermissions([
            'dashboard.view',

            'member.view',

            'saving.view',

            'loan.view',

            'installment.view',
        ]);
    }
}