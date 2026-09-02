<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingTypeRequest;
use App\Http\Requests\UpdateSavingTypeRequest;
use App\Models\SavingType;
use App\Models\Account;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingTypeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SavingType::class);

        $query = SavingType::query()->latest('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        $savingTypes = $query
            ->paginate(15)
            ->withQueryString();

        $totalTypes = SavingType::count();
        $activeTypes = SavingType::where('is_active', true)->count();
        $mandatoryTypes = SavingType::where('is_mandatory', true)->count();

        return view('saving-types.index', compact(
            'savingTypes',
            'totalTypes',
            'activeTypes',
            'mandatoryTypes'
        ));
    }

	public function create(): View
	{
		$this->authorize('create', SavingType::class);

		$liabilityAccounts = Account::query()
			->where('type', Account::TYPE_LIABILITY)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		return view(
			'saving-types.create',
			compact('liabilityAccounts')
		);
	}

    public function store(
        StoreSavingTypeRequest $request
    ): RedirectResponse {
        $this->authorize('create', SavingType::class);

        $savingType = SavingType::create($request->validated());

        $this->audit(
            'CREATE',
            $savingType,
            'Menambahkan jenis simpanan ' . $savingType->code,
            [],
            $savingType->only([
                'code',
                'name',
                'description',
                'amount',
                'is_mandatory',
                'is_withdrawable',
                'is_active',
            ])
        );

        return redirect()
            ->route('saving-types.index')
            ->with('success', 'Jenis simpanan berhasil ditambahkan.');
    }

    public function show(SavingType $savingType): View
    {
        $this->authorize('view', $savingType);

		$savingType->loadMissing([
			'liabilityAccount',
		]);

        return view('saving-types.show', compact('savingType'));
    }

	public function edit(SavingType $savingType): View
	{
		$this->authorize('update', $savingType);

		$liabilityAccounts = Account::query()
			->where('type', Account::TYPE_LIABILITY)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		return view(
			'saving-types.edit',
			compact(
				'savingType',
				'liabilityAccounts'
			)
		);
	}

    public function update(
        UpdateSavingTypeRequest $request,
        SavingType $savingType
    ): RedirectResponse {
        $this->authorize('update', $savingType);

        $oldValues = $savingType->only([
            'code',
            'name',
            'description',
            'amount',
            'is_mandatory',
            'is_withdrawable',
            'is_active',
        ]);

        $savingType->update($request->validated());

        $this->audit(
            'UPDATE',
            $savingType,
            'Mengubah jenis simpanan ' . $savingType->code,
            $oldValues,
            $savingType->only(array_keys($oldValues))
        );

        return redirect()
            ->route('saving-types.show', $savingType)
            ->with('success', 'Jenis simpanan berhasil diperbarui.');
    }

    public function toggleStatus(
        Request $request,
        SavingType $savingType
    ): RedirectResponse {
        $this->authorize('update', $savingType);

        $oldStatus = $savingType->is_active;

        $savingType->update([
            'is_active' => ! $savingType->is_active,
        ]);

        $this->audit(
            $savingType->is_active ? 'ACTIVE' : 'INACTIVE',
            $savingType,
            ($savingType->is_active ? 'Mengaktifkan' : 'Menonaktifkan')
                . ' jenis simpanan ' . $savingType->code,
            ['is_active' => $oldStatus],
            ['is_active' => $savingType->is_active]
        );

        return back()->with(
            'success',
            $savingType->is_active
                ? 'Jenis simpanan berhasil diaktifkan.'
                : 'Jenis simpanan berhasil dinonaktifkan.'
        );
    }

    private function audit(
        string $action,
        SavingType $savingType,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->log(
            action: $action,
            model: $savingType,
            description: $description,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}
