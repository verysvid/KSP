<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class MemberUserController extends Controller
{
    use AuthorizesRequests;

    public function create(Member $member): View|RedirectResponse
    {
        $this->authorize('view', $member);
        abort_unless(auth()->user()?->can('user.create'), 403);

        $member->load(['branch', 'user']);

        if ($member->user_id && $member->user) {
            return redirect()
                ->route('users.show', $member->user)
                ->with('info', 'Anggota ini sudah memiliki user login.');
        }

        if ($member->member_status !== 'ACTIVE') {
            abort(422, 'Hanya anggota aktif yang dapat dibuatkan user login.');
        }

		$roles = Role::query()
			->whereIn('name', [
				'Manager',
				'Pengurus',
				'Accounting',
				'Anggota',
			])
			->orderBy('name')
			->get();


        return view('members.add-to-user', compact('member', 'roles'));
    }

    public function store(Request $request, Member $member): RedirectResponse
    {
        $this->authorize('view', $member);
        abort_unless($request->user()?->can('user.create'), 403);

        $member->refresh();

        if ($member->user_id) {
            return redirect()
                ->route('members.show', $member)
                ->with('info', 'Anggota ini sudah memiliki user login.');
        }

        if ($member->member_status !== 'ACTIVE') {
            abort(422, 'Hanya anggota aktif yang dapat dibuatkan user login.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
				Rule::in([
						'Manager',
						'Pengurus',
						'Accounting',
						'Anggota',
					]),
            ],
        ]);

        $user = DB::transaction(function () use ($member, $validated) {
            $user = User::create([
                'branch_id' => $member->branch_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            $user->syncRoles([$validated['role']]);

            $member->update([
                'user_id' => $user->id,
            ]);

            return $user;
        });

        $this->audit(
            'CREATE',
            $user,
            'Membuat user login dari anggota ' . $member->member_number,
            [],
            [
                'member_id' => $member->id,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
            ]
        );

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'User login anggota berhasil dibuat.');
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
