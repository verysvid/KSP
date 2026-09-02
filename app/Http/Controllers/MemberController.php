<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberType;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Member::class);

        $query = Member::query()
            ->with([
                'branch',
                'user',
                'memberType',
            ]);

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        if ($branchId !== null) {
            $query->where(
                'branch_id',
                $branchId
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'member_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'nik',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'member_status',
                $request->status
            );
        }

        $members = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statsQuery = Member::query();

        if ($branchId !== null) {
            $statsQuery->where(
                'branch_id',
                $branchId
            );
        }

        $totalMembers = (clone $statsQuery)
            ->count();

        $activeMembers = (clone $statsQuery)
            ->where(
                'member_status',
                'ACTIVE'
            )
            ->count();

        $inactiveMembers = (clone $statsQuery)
            ->where(
                'member_status',
                'INACTIVE'
            )
            ->count();

        return view(
            'members.index',
            compact(
                'members',
                'totalMembers',
                'activeMembers',
                'inactiveMembers'
            )
        );
    }

    public function create(): View
    {
        $this->authorize(
            'create',
            Member::class
        );

        $isSuperAdmin = $this->branchContext
            ->isSuperAdmin();

        if ($isSuperAdmin) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $currentBranch = null;
        } else {
            $currentBranch = $this->branchContext
                ->getCurrentBranch();

            if (! $currentBranch) {
                abort(
                    403,
                    'User belum memiliki cabang.'
                );
            }

            $branches = collect([
                $currentBranch,
            ]);
        }

        $memberTypes = MemberType::query()
            ->orderBy('name')
            ->get();

        return view(
            'members.create',
            compact(
                'branches',
                'memberTypes',
                'isSuperAdmin',
                'currentBranch'
            )
        );
    }

    public function store(
        StoreMemberRequest $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize(
            'create',
            Member::class
        );

        $data = $request->validated();

        if ($this->branchContext->isSuperAdmin()) {
            if (empty($data['branch_id'])) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Cabang wajib dipilih.',
                ]);
            }

            $branchExists = Branch::query()
                ->whereKey($data['branch_id'])
                ->where('is_active', true)
                ->exists();

            if (! $branchExists) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Cabang tidak aktif atau tidak valid.',
                ]);
            }
        } else {
            $data['branch_id'] = $this
                ->branchContext
                ->getCurrentBranchId();

            if (! $data['branch_id']) {
                abort(
                    403,
                    'User belum memiliki cabang.'
                );
            }
        }

        $member = Member::create($data);

        $auditLog->log(
            action: 'CREATE',
            model: $member,
            description:
                'Menambahkan anggota '
                . $member->member_number,
            oldValues: [],
            newValues: $member->only([
                'branch_id',
                'member_type_id',
                'member_number',
                'nik',
                'name',
                'gender',
                'birth_place',
                'birth_date',
                'address',
                'phone',
                'email',
                'occupation',
                'amount_saving',
                'join_date',
                'member_status',
                'notes',
            ])
        );

        return redirect()
            ->route('members.index')
            ->with(
                'success',
                'Anggota berhasil ditambahkan.'
            );
    }

    public function show(
        Member $member
    ): View {
        $this->authorize(
            'view',
            $member
        );

        $this->ensureMemberAccess(
            $member
        );

        $member->load([
            'branch',
            'user',
            'memberType',
        ]);

        return view(
            'members.show',
            compact('member')
        );
    }

    public function edit(
        Member $member
    ): View {
        $this->authorize(
            'update',
            $member
        );

        $this->ensureMemberAccess(
            $member
        );

        $member->load([
            'branch',
            'user',
            'memberType',
        ]);

        $isSuperAdmin = $this->branchContext
            ->isSuperAdmin();

        if ($isSuperAdmin) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $currentBranch = null;
        } else {
            $currentBranch = $this->branchContext
                ->getCurrentBranch();

            if (! $currentBranch) {
                abort(
                    403,
                    'User belum memiliki cabang.'
                );
            }

            $branches = collect([
                $currentBranch,
            ]);
        }

        $memberTypes = MemberType::query()
            ->orderBy('name')
            ->get();

        return view(
            'members.edit',
            compact(
                'member',
                'branches',
                'memberTypes',
                'isSuperAdmin',
                'currentBranch'
            )
        );
    }

    public function update(
        UpdateMemberRequest $request,
        Member $member,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize(
            'update',
            $member
        );

        $this->ensureMemberAccess(
            $member
        );

        $data = $request->validated();

        if ($this->branchContext->isSuperAdmin()) {
            if (empty($data['branch_id'])) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Cabang wajib dipilih.',
                ]);
            }

            $branchExists = Branch::query()
                ->whereKey($data['branch_id'])
                ->where('is_active', true)
                ->exists();

            if (! $branchExists) {
                throw ValidationException::withMessages([
                    'branch_id' =>
                        'Cabang tidak aktif atau tidak valid.',
                ]);
            }
        } else {
            $data['branch_id'] = $this
                ->branchContext
                ->getCurrentBranchId();

            if (! $data['branch_id']) {
                abort(
                    403,
                    'User belum memiliki cabang.'
                );
            }
        }

        $fields = [
            'branch_id',
            'member_type_id',
            'name',
            'nik',
            'gender',
            'birth_place',
            'birth_date',
            'address',
            'phone',
            'email',
            'occupation',
            'amount_saving',
            'join_date',
            'member_status',
            'notes',
        ];

        $oldValues = $member->only(
            $fields
        );

        $member->update($data);
        $member->refresh();

        $auditLog->log(
            action: 'UPDATE',
            model: $member,
            description:
                'Mengubah data anggota '
                . $member->member_number,
            oldValues: $oldValues,
            newValues: $member->only(
                $fields
            )
        );

        return redirect()
            ->route(
                'members.show',
                $member
            )
            ->with(
                'success',
                'Data anggota berhasil diperbarui.'
            );
    }

    public function destroy(
        Member $member,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize(
            'delete',
            $member
        );

        $this->ensureMemberAccess(
            $member
        );

        $oldValues = $member->only([
            'member_status',
        ]);

        $member->update([
            'member_status' => 'INACTIVE',
        ]);

        $auditLog->log(
            action: 'INACTIVE',
            model: $member,
            description:
                'Menonaktifkan anggota '
                . $member->member_number,
            oldValues: $oldValues,
            newValues: [
                'member_status' =>
                    $member->member_status,
            ]
        );

        return redirect()
            ->route('members.index')
            ->with(
                'success',
                'Anggota berhasil dinonaktifkan.'
            );
    }

    private function ensureMemberAccess(
        Member $member
    ): void {
        if ($this->branchContext->isSuperAdmin()) {
            return;
        }

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        abort_unless(
            $branchId !== null
            && (int) $member->branch_id
                === (int) $branchId,
            403
        );
    }
}