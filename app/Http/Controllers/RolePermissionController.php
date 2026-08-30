<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('role.view'), 403);

        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Request $request, Role $role): View
    {
        abort_unless($request->user()?->can('role.edit'), 403);

        if ($role->name === 'SuperAdmin' && ! $request->user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        $rolePermissionNames = $role->permissions()
            ->pluck('name')
            ->all();

        return view('roles.edit', compact(
            'role',
            'permissions',
            'rolePermissionNames'
        ));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()?->can('role.edit'), 403);

        if ($role->name === 'SuperAdmin' && ! $request->user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissions = $validated['permissions'] ?? [];

        $role->syncPermissions($permissions);

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Permission role berhasil diperbarui.');
    }
}
