<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'branch.view', 'branch.create', 'branch.edit', 'branch.delete',
            'member.view', 'member.create', 'member.edit', 'member.delete',
            'saving-type.view', 'saving-type.create', 'saving-type.edit',
            'saving.view', 'saving.create', 'saving.edit', 'saving.delete',
            'saving-transaction.view', 'saving-transaction.create',
            'saving-transaction.approve', 'saving-transaction.reject',
            'loan.view', 'loan.create', 'loan.edit', 'loan.delete', 'loan.submit',
            'loan.approve', 'loan.reject', 'loan.disburse', 'loan.pay',
            'member-loan-application.view',
            'member-loan-application.create',
            'member-loan-application.edit',
            'member-loan-application.delete',
            'member-loan-application.submit',
            'installment.view', 'installment.create', 'installment.edit',
            'bulk-transaction.view', 'bulk-transaction.process',
            'accounting.view',
            'account.view', 'account.create', 'account.edit',
            'journal.view', 'journal.create', 'journal.edit',
            'report.view', 'report.member-deductions.view',
            'member-saving-report.view', 'member-loan-report.view',
            'shu.view', 'shu.process', 'closing.view', 'closing.process',
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.restore',
            'role.view', 'role.edit', 'audit-log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $pengurus = Role::firstOrCreate(['name' => 'Pengurus', 'guard_name' => 'web']);
        $accounting = Role::firstOrCreate(['name' => 'Accounting', 'guard_name' => 'web']);
        $anggota = Role::firstOrCreate(['name' => 'Anggota', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::all());

        $managerPengurusPermissions = [
            'dashboard.view', 'branch.view',
            'member.view', 'member.create', 'member.edit',
            'saving-type.view', 'saving-type.create', 'saving-type.edit',
            'saving-transaction.view', 'saving-transaction.create',
            'saving-transaction.approve', 'saving-transaction.reject',
            'loan.view', 'loan.create', 'loan.edit', 'loan.submit', 'loan.approve',
            'loan.reject', 'loan.disburse', 'loan.pay',
            'installment.view', 'installment.create', 'installment.edit',
            'bulk-transaction.view', 'bulk-transaction.process',
            'accounting.view',
            'account.view', 'account.create', 'account.edit',
            'journal.view', 'journal.create', 'journal.edit',
            'report.view', 'report.member-deductions.view',
            'member-saving-report.view', 'member-loan-report.view',
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.restore',
            'audit-log.view',
        ];

        $manager->syncPermissions($managerPengurusPermissions);
        $pengurus->syncPermissions($managerPengurusPermissions);

        $accounting->syncPermissions([
            'dashboard.view', 'saving.view', 'saving-transaction.view',
            'loan.view', 'installment.view', 'bulk-transaction.view',
            'accounting.view', 'account.view', 'account.create', 'account.edit',
            'journal.view', 'journal.create', 'journal.edit', 'report.view',
            'shu.view', 'shu.process', 'closing.view', 'closing.process',
        ]);

        $anggota->syncPermissions([
            'member-saving-report.view',
            'member-loan-report.view',
            'member-loan-application.view',
            'member-loan-application.create',
            'member-loan-application.edit',
            'member-loan-application.delete',
            'member-loan-application.submit',
        ]);
    }
}
