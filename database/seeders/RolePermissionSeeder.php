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

            // Branch
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',

            // Anggota
            'member.view',
            'member.create',
            'member.edit',
            'member.delete',

            // Jenis Simpanan
            'saving-type.view',
            'saving-type.create',
            'saving-type.edit',

            // Simpanan
            'saving.view',
            'saving.create',
            'saving.edit',
            'saving.delete',

            // Transaksi Simpanan
            'saving-transaction.view',
            'saving-transaction.create',
            'saving-transaction.approve',
            'saving-transaction.reject',

            // Pinjaman
            'loan.view',
            'loan.create',
            'loan.edit',
            'loan.delete',
            'loan.submit',
            'loan.approve',
            'loan.reject',
            'loan.disburse',
            'loan.pay',

            // Angsuran
            'installment.view',
            'installment.create',
            'installment.edit',

			// Accounting
			'accounting.view',

			// Chart Of Account
			'account.view',
			'account.create',
			'account.edit',

			// Journal
			'journal.view',
			'journal.create',
			'journal.edit',

            // Reports
            'report.view',
            'member-saving-report.view',
            'member-loan-report.view',

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
            'user.restore',

            // Role Management
            'role.view',
            'role.edit',

            // Audit Log
            'audit-log.view',
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
        | Manager & Pengurus
        |--------------------------------------------------------------------------
        */

        $managerPengurusPermissions = [

            // Dashboard
            'dashboard.view',

            // Cabang
            'branch.view',

            // Anggota
            'member.view',
            'member.create',
            'member.edit',

            // Jenis Simpanan
            'saving-type.view',
            'saving-type.create',
            'saving-type.edit',

            // Transaksi Simpanan
            'saving-transaction.view',
            'saving-transaction.create',
            'saving-transaction.approve',
            'saving-transaction.reject',

            // Pinjaman
            'loan.view',
            'loan.create',
            'loan.edit',
            'loan.submit',
            'loan.approve',
            'loan.reject',
            'loan.disburse',

            // Pembayaran Angsuran
            'installment.view',
            'installment.create',
            'installment.edit',
            'loan.pay',

			// Accounting
			'accounting.view',

			// Chart Of Account
			'account.view',
			'account.create',
			'account.edit',

			// Journal
			'journal.view',
			'journal.create',
			'journal.edit',

            // Reports
            'report.view',

            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.restore',

            // Audit Log
            'audit-log.view',
        ];

        $manager->syncPermissions(
            $managerPengurusPermissions
        );

        $pengurus->syncPermissions(
            $managerPengurusPermissions
        );

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        $accounting->syncPermissions([
            'dashboard.view',

            'saving.view',
            'saving-transaction.view',

            'loan.view',
            'installment.view',

            'accounting.view',

			'account.view',
			'account.create',
			'account.edit',

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
            'member-saving-report.view',
            'member-loan-report.view',
        ]);
    }
}