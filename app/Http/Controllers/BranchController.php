<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('branch.view'), 403);

        $query = Branch::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('manager_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === '1');
        }

        $branches = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalBranches = Branch::count();
        $activeBranches = Branch::where('is_active', true)->count();
        $inactiveBranches = Branch::where('is_active', false)->count();
		
        return view('branches.index', compact(
            'branches',
            'totalBranches',
            'activeBranches',
            'inactiveBranches'
        ));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('branch.create'), 403);

        return view('branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($request->validated());

        $this->audit(
            'CREATE',
            $branch,
            'Menambahkan cabang ' . $branch->code,
            [],
            $branch->only([
                'code', 'name', 'address', 'phone',
                'email', 'manager_name', 'is_active',
            ])
        );

        return redirect()
            ->route('branches.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function show(Request $request, Branch $branch): View
    {
        abort_unless($request->user()?->can('branch.view'), 403);

        $branch->loadCount(['users', 'members']);

        return view('branches.show', compact('branch'));
    }

    public function edit(Request $request, Branch $branch): View
    {
        abort_unless($request->user()?->can('branch.edit'), 403);

        return view('branches.edit', compact('branch'));
    }

    public function update(
        UpdateBranchRequest $request,
        Branch $branch
    ): RedirectResponse {
        $oldValues = $branch->only([
            'code', 'name', 'address', 'phone',
            'email', 'manager_name', 'is_active',
        ]);

        $branch->update($request->validated());

        $this->audit(
            'UPDATE',
            $branch,
            'Mengubah cabang ' . $branch->code,
            $oldValues,
            $branch->only(array_keys($oldValues))
        );

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Branch $branch): RedirectResponse
    {
        abort_unless($request->user()?->can('branch.edit'), 403);

        $oldStatus = $branch->is_active;

        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        $this->audit(
            $branch->is_active ? 'ACTIVE' : 'INACTIVE',
            $branch,
            ($branch->is_active ? 'Mengaktifkan' : 'Menonaktifkan')
                . ' cabang ' . $branch->code,
            ['is_active' => $oldStatus],
            ['is_active' => $branch->is_active]
        );

        return back()->with(
            'success',
            $branch->is_active
                ? 'Cabang berhasil diaktifkan.'
                : 'Cabang berhasil dinonaktifkan.'
        );
    }

    private function audit(
        string $action,
        Branch $branch,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->log(
            action: $action,
            model: $branch,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}
