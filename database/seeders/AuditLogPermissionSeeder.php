<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::findOrCreate('audit-log.view');
        if ($role = Role::where('name','SuperAdmin')->first()) $role->givePermissionTo($permission);
        if ($role = Role::where('name','Manager')->first()) $role->givePermissionTo($permission);
    }
}
