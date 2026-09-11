<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberType;
use App\Models\SavingType;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use App\Services\MemberActivationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('member_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
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

        $totalMembers = (clone $statsQuery)->count();

        $activeMembers = (clone $statsQuery)
            ->where('member_status', 'ACTIVE')
            ->count();

        $newMembers = (clone $statsQuery)
            ->where('member_status', 'NEW')
            ->count();

        $inactiveMembers = (clone $statsQuery)
            ->where('member_status', 'INACTIVE')
            ->count();

        return view(
            'members.index',
            compact(
                'members',
                'totalMembers',
                'activeMembers',
                'newMembers',
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

        [$savingPokok, $savingWajib] = $this->getRegistrationSavingTypes();

        return view(
            'members.create',
            compact(
                'branches',
                'memberTypes',
                'isSuperAdmin',
                'currentBranch',
                'savingPokok',
                'savingWajib'
            )
        );
    }

    public function store(
        StoreMemberRequest $request,
        AuditLogService $auditLog,
        MemberActivationService $activationService
    ): RedirectResponse {
        $this->authorize('create', Member::class);

        $data = $request->validated();
        unset($data['agreement']);

        if ($this->branchContext->isSuperAdmin()) {
            if (empty($data['branch_id'])) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Cabang wajib dipilih.',
                ]);
            }

            $branchExists = Branch::query()
                ->whereKey($data['branch_id'])
                ->where('is_active', true)
                ->exists();

            if (! $branchExists) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Cabang tidak aktif atau tidak valid.',
                ]);
            }
        } else {
            $data['branch_id'] = $this->branchContext
                ->getCurrentBranchId();

            if (! $data['branch_id']) {
                abort(
                    403,
                    'User belum memiliki cabang.'
                );
            }
        }

        if ($request->hasFile('id_card_image')) {
            $data['id_card_image'] = $request
                ->file('id_card_image')
                ->store('members/ktp', 'public');
        }

        $member = DB::transaction(function () use (
            $data,
            $activationService
        ) {
            $data['member_status'] = 'NEW';

            $member = Member::create($data);

            $activationService->activate($member);

            return $member->fresh();
        });

        $auditLog->log(
            action: 'ACTIVATE',
            model: $member,
            description: 'Menambahkan dan mengaktifkan anggota ' . $member->member_number,
            oldValues: [],
            newValues: $member->only([
                'branch_id',
                'member_type_id',
                'member_number',
                'name',
                'email',
                'work_unit',
                'id_card_image',
                'amount_saving',
                'join_date',
                'member_status',
                'user_id',
            ])
        );

        return redirect()
            ->route('members.index')
            ->with(
                'success',
                'Anggota berhasil dibuat dan diaktifkan. Akun login telah dibuat dengan password awal password123.'
            );
    }

    public function activate(
        Member $member,
        AuditLogService $auditLog,
        MemberActivationService $activationService
    ): RedirectResponse {
        $this->authorize('update', $member);
        $this->ensureMemberAccess($member);

        if ($member->member_status !== 'NEW') {
            throw ValidationException::withMessages([
                'member' => 'Hanya anggota dengan status NEW yang dapat diaktivasi.',
            ]);
        }

        $oldValues = $member->only([
            'member_status',
            'user_id',
        ]);

        $activationService->activate($member);
        $member->refresh();

        $auditLog->log(
            action: 'ACTIVATE',
            model: $member,
            description: 'Mengaktifkan anggota ' . $member->member_number . ' dan membuat akun login.',
            oldValues: $oldValues,
            newValues: $member->only([
                'member_status',
                'user_id',
            ])
        );

        return redirect()
            ->route('members.index')
            ->with(
                'success',
                'Anggota berhasil diaktifkan. Akun login telah dibuat dengan password awal password123.'
            );
    }

    public function show(Member $member): View
    {
        $this->authorize(
            'view',
            $member
        );

        $this->ensureMemberAccess($member);

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

    public function edit(Member $member): View
    {
        $this->authorize(
            'update',
            $member
        );

        $this->ensureMemberAccess($member);
		$this->ensureMemberEditable($member);

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

        [$savingPokok, $savingWajib] = $this->getRegistrationSavingTypes();

        return view(
            'members.edit',
            compact(
                'member',
                'branches',
                'memberTypes',
                'isSuperAdmin',
                'currentBranch',
                'savingPokok',
                'savingWajib'
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

        $this->ensureMemberAccess($member);
		$this->ensureMemberEditable($member);

        $data = $request->validated();

        if ($this->branchContext->isSuperAdmin()) {
            if (empty($data['branch_id'])) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Cabang wajib dipilih.',
                ]);
            }

            $branchExists = Branch::query()
                ->whereKey($data['branch_id'])
                ->where('is_active', true)
                ->exists();

            if (! $branchExists) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Cabang tidak aktif atau tidak valid.',
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

        $oldIdCardImage = $member->id_card_image;

        if ($request->hasFile('id_card_image')) {
            $data['id_card_image'] = $request
                ->file('id_card_image')
                ->store('members/ktp', 'public');
        } else {
            unset($data['id_card_image']);
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
            'work_unit',
            'id_card_image',
            'amount_saving',
            'join_date',
            'member_status',
            'notes',
        ];

        $oldValues = $member->only($fields);

        DB::transaction(function () use (
            $member,
            $data
        ) {
            $member->update($data);

            $member->refresh();

            if ($member->user_id) {
                if ($member->member_status === 'INACTIVE') {
                    $member->user()
                        ->update([
                            'is_active' => false,
                        ]);
                }

                if ($member->member_status === 'ACTIVE') {
                    $member->user()
                        ->update([
                            'is_active' => true,
                        ]);
                }
            }
        });

        if (
            isset($data['id_card_image'])
            && $oldIdCardImage
            && $oldIdCardImage !== $data['id_card_image']
        ) {
            Storage::disk('public')->delete($oldIdCardImage);
        }

        $member->refresh();

        $auditLog->log(
            action: 'UPDATE',
            model: $member,
            description: 'Mengubah data anggota ' . $member->member_number,
            oldValues: $oldValues,
            newValues: $member->only($fields)
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

        $this->ensureMemberAccess($member);

        $oldValues = $member->only([
            'member_status',
        ]);

        DB::transaction(function () use ($member) {
            $member->update([
                'member_status' => 'INACTIVE',
            ]);

            if ($member->user_id) {
                $member->user()
                    ->update([
                        'is_active' => false,
                    ]);
            }
        });

        $member->refresh();

        $auditLog->log(
            action: 'INACTIVE',
            model: $member,
            description:
                'Menonaktifkan anggota '
                . $member->member_number
                . ' beserta akun login.',
            oldValues: $oldValues,
            newValues: [
                'member_status' => $member->member_status,
                'user_is_active' => false,
            ]
        );

        return redirect()
            ->route('members.index')
            ->with(
                'success',
                'Anggota dan akun login berhasil dinonaktifkan.'
            );
    }

    private function getRegistrationSavingTypes(): array
    {
        $savingPokok = SavingType::query()
            ->where('is_active', true)
            ->where('code', 'POKOK')
            ->first();

        $savingWajib = SavingType::query()
            ->where('is_active', true)
            ->where('code', 'WAJIB')
            ->first();

        return [
            $savingPokok,
            $savingWajib,
        ];
    }

	private function ensureMemberEditable(
		Member $member
	): void {
		abort_if(
			$member->member_status === 'NEW',
			403,
			'Anggota dengan status NEW tidak dapat diedit sebelum diaktivasi.'
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
            && (int) $member->branch_id === (int) $branchId,
            403
        );
    }
}
