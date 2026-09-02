<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesRequests;

    private const MANAGEABLE_ROLES = [
        'Manager',
        'Pengurus',
        'Accounting',
        'Anggota',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $currentUser = $request->user();

        $query = User::query()
            ->with(['branch', 'roles'])
            ->latest('id');

        if (! $currentUser->hasRole('SuperAdmin')) {
            $this->applyBranchUserScope(
                $query,
                $currentUser
            );
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        if ($request->filled('role')) {
            $role = (string) $request->input('role');

            if (! $currentUser->hasRole('SuperAdmin')) {
                abort_unless(
                    in_array(
                        $role,
                        self::MANAGEABLE_ROLES,
                        true
                    ),
                    403
                );
            }

            $query->role($role);
        }

        $users = $query
            ->paginate(15)
            ->withQueryString();

        $statsQuery = User::query();

        if (! $currentUser->hasRole('SuperAdmin')) {
            $this->applyBranchUserScope(
                $statsQuery,
                $currentUser
            );
        }

        $totalUsers = (clone $statsQuery)
            ->count();

        $activeUsers = (clone $statsQuery)
            ->where('is_active', true)
            ->count();

        $inactiveUsers = (clone $statsQuery)
            ->where('is_active', false)
            ->count();

        $roles = $this->assignableRoles(
            $currentUser
        );

        return view('users.index', compact(
            'users',
            'roles',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $currentUser = $request->user();

        $branches = $currentUser->hasRole('SuperAdmin')
            ? Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : Branch::query()
                ->whereKey($currentUser->branch_id)
                ->get();

        $roles = $this->assignableRoles(
            $currentUser
        );

        return view(
            'users.create',
            compact('branches', 'roles')
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $this->authorize('create', User::class);

        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'role' => [
                'required',
                'string',
                'exists:roles,name',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $this->assertBranchAccess(
            $currentUser,
            (int) $validated['branch_id']
        );

        $this->assertRoleAssignable(
            $currentUser,
            $validated['role']
        );

        $newUser = DB::transaction(
            function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'branch_id' => $validated['branch_id'],
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                $user->syncRoles([
                    $validated['role'],
                ]);

                return $user;
            }
        );

        $this->audit(
            'CREATE',
            $newUser,
            'Membuat user ' . $newUser->email,
            [],
            [
                'name' => $newUser->name,
                'email' => $newUser->email,
                'branch_id' => $newUser->branch_id,
                'is_active' => $newUser->is_active,
                'roles' => $newUser
                    ->getRoleNames()
                    ->values()
                    ->all(),
            ]
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dibuat.'
            );
    }

    public function show(
        Request $request,
        User $user
    ): View {
        $this->authorize('view', $user);

        $this->assertUserManageable(
            $request->user(),
            $user
        );

        $user->load([
            'branch',
            'roles.permissions',
        ]);

        return view(
            'users.show',
            compact('user')
        );
    }

    public function edit(
        Request $request,
        User $user
    ): View {
        $this->authorize('update', $user);

        $currentUser = $request->user();

        $this->assertUserManageable(
            $currentUser,
            $user
        );

        $branches = $currentUser->hasRole('SuperAdmin')
            ? Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : Branch::query()
                ->whereKey($currentUser->branch_id)
                ->get();

        $roles = $this->assignableRoles(
            $currentUser
        );

        return view(
            'users.edit',
            compact(
                'user',
                'branches',
                'roles'
            )
        );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->authorize('update', $user);

        $currentUser = $request->user();

        $this->assertUserManageable(
            $currentUser,
            $user
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'role' => [
                'required',
                'string',
                'exists:roles,name',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $this->assertBranchAccess(
            $currentUser,
            (int) $validated['branch_id']
        );

        $this->assertRoleAssignable(
            $currentUser,
            $validated['role']
        );

        if (
            $user->id === $currentUser->id
            && ! ($validated['is_active'] ?? false)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'is_active' =>
                        'Anda tidak dapat menonaktifkan akun Anda sendiri.',
                ]);
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'branch_id' => $user->branch_id,
            'is_active' => $user->is_active,
            'roles' => $user
                ->getRoleNames()
                ->values()
                ->all(),
        ];

        DB::transaction(
            function () use (
                $user,
                $validated
            ) {
                $data = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'branch_id' => $validated['branch_id'],
                    'is_active' =>
                        $validated['is_active'] ?? false,
                ];

                if (! empty($validated['password'])) {
                    $data['password'] =
                        $validated['password'];
                }

                $user->update($data);

                $user->syncRoles([
                    $validated['role'],
                ]);
            }
        );

        $user->refresh();

        $this->audit(
            'UPDATE',
            $user,
            'Mengubah user ' . $user->email,
            $oldValues,
            [
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'is_active' => $user->is_active,
                'roles' => $user
                    ->getRoleNames()
                    ->values()
                    ->all(),
            ]
        );

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                'User berhasil diperbarui.'
            );
    }

    public function destroy(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->authorize('delete', $user);

        $currentUser = $request->user();

        $this->assertUserManageable(
            $currentUser,
            $user
        );

        if ($user->id === $currentUser->id) {
            return back()->withErrors([
                'user' =>
                    'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);
        }

        if (! $user->is_active) {
            return back()->with(
                'info',
                'User tersebut sudah tidak aktif.'
            );
        }

        $user->update([
            'is_active' => false,
        ]);

        $this->audit(
            'INACTIVE',
            $user,
            'Menonaktifkan user ' . $user->email,
            [
                'is_active' => true,
            ],
            [
                'is_active' => false,
            ]
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dinonaktifkan.'
            );
    }

    public function restore(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->authorize('restore', $user);

        $this->assertUserManageable(
            $request->user(),
            $user
        );

        if ($user->is_active) {
            return back()->with(
                'info',
                'User tersebut sudah aktif.'
            );
        }

        $user->update([
            'is_active' => true,
        ]);

        $this->audit(
            'ACTIVE',
            $user,
            'Mengaktifkan user ' . $user->email,
            [
                'is_active' => false,
            ],
            [
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil diaktifkan.'
            );
    }

    private function assignableRoles(
        User $currentUser
    ) {
        $query = Role::query()
            ->orderBy('name');

        if (! $currentUser->hasRole('SuperAdmin')) {
            $query->whereIn(
                'name',
                self::MANAGEABLE_ROLES
            );
        }

        return $query->get();
    }

    private function applyBranchUserScope(
        $query,
        User $currentUser
    ): void {
        $query
            ->where(
                'branch_id',
                $currentUser->branch_id
            )
            ->whereHas(
                'roles',
                function ($roleQuery) {
                    $roleQuery->whereIn(
                        'name',
                        self::MANAGEABLE_ROLES
                    );
                }
            )
            ->whereDoesntHave(
                'roles',
                function ($roleQuery) {
                    $roleQuery->whereNotIn(
                        'name',
                        self::MANAGEABLE_ROLES
                    );
                }
            );
    }

    private function assertUserManageable(
        User $currentUser,
        User $targetUser
    ): void {
        if ($currentUser->hasRole('SuperAdmin')) {
            return;
        }

        abort_unless(
            $currentUser->branch_id !== null
            && (int) $currentUser->branch_id
                === (int) $targetUser->branch_id,
            403
        );

        $targetRoles = $targetUser
            ->getRoleNames()
            ->values()
            ->all();

        abort_if(
            empty($targetRoles),
            403
        );

        foreach ($targetRoles as $role) {
            abort_unless(
                in_array(
                    $role,
                    self::MANAGEABLE_ROLES,
                    true
                ),
                403
            );
        }
    }

    private function assertRoleAssignable(
        User $currentUser,
        string $role
    ): void {
        if ($currentUser->hasRole('SuperAdmin')) {
            return;
        }

        abort_unless(
            in_array(
                $role,
                self::MANAGEABLE_ROLES,
                true
            ),
            403
        );
    }

    private function assertBranchAccess(
        User $currentUser,
        int $branchId
    ): void {
        if ($currentUser->hasRole('SuperAdmin')) {
            return;
        }

        abort_unless(
            $currentUser->branch_id !== null
            && (int) $currentUser->branch_id
                === $branchId,
            403
        );
    }

    private function audit(
        string $action,
        User $user,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->log(
            action: $action,
            model: $user,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}