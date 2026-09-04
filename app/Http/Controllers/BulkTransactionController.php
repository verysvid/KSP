<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessBulkTransactionRequest;
use App\Models\Branch;
use App\Models\BulkTransaction;
use App\Services\BranchContext;
use App\Services\BulkTransactionPreviewService;
use App\Services\BulkTransactionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BulkTransactionController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected BulkTransactionPreviewService $previewService,
        protected BulkTransactionService $bulkTransactionService,
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('bulk-transaction.view'), 403);
        [$month, $year] = $this->validatedPeriod($request);
        $branchId = $this->resolveBranchId($request, false);
        $branch = $branchId ? Branch::find($branchId) : null;
        $report = $branchId ? $this->previewService->generate($branchId, $month, $year) : null;

        $periodDate = Carbon::create($year, $month, 1);
        $defaultDate = $periodDate->isSameMonth(now()) ? now() : $periodDate->copy()->endOfMonth();

        $batches = BulkTransaction::query()
            ->with(['branch:id,code,name', 'processor:id,name'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('transaction_date')->latest('id')->paginate(10)->withQueryString();

        return view('bulk-transactions.index', [
            'month' => $month, 'year' => $year, 'branch' => $branch,
            'branches' => $this->availableBranches(),
            'isSuperAdmin' => $this->branchContext->isSuperAdmin(),
            'currentBranch' => $this->branchContext->getCurrentBranch(),
            'report' => $report, 'batches' => $batches,
            'defaultTransactionDate' => old('transaction_date', $defaultDate->format('Y-m-d')),
        ]);
    }

    public function store(ProcessBulkTransactionRequest $request): RedirectResponse
    {
        $branchId = $this->resolveBranchId($request, true);
        $data = $request->validated();
        $batch = $this->bulkTransactionService->process(
            $branchId, (int) $data['month'], (int) $data['year'],
            $data['transaction_date'], $data['member_ids'], (int) $request->user()->id
        );

        return redirect()->route('bulk-transactions.show', $batch)
            ->with('success', "Transaksi Bulk {$batch->batch_no} berhasil diproses.");
    }

    public function show(Request $request, BulkTransaction $bulkTransaction): View
    {
        abort_unless($request->user()?->can('bulk-transaction.view'), 403);
        if (!$this->branchContext->isSuperAdmin()) {
            abort_unless((int) $bulkTransaction->branch_id === (int) $this->branchContext->getCurrentBranchId(), 403);
        }
        $bulkTransaction->load([
            'branch:id,code,name', 'processor:id,name',
            'members.member:id,member_number,name',
            'members.items.savingTransaction:id,trx_no,journal_entry_id',
            'members.items.loanPayment:id,payment_no,journal_entry_id',
        ]);
        return view('bulk-transactions.show', compact('bulkTransaction'));
    }

    private function validatedPeriod(Request $request): array
    {
        $validated = validator([
            'month' => $request->input('month', now()->month),
            'year' => $request->input('year', now()->year),
        ], [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ])->validate();
        return [(int) $validated['month'], (int) $validated['year']];
    }

    private function resolveBranchId(Request $request, bool $required): ?int
    {
        if (!$this->branchContext->isSuperAdmin()) {
            $branchId = $this->branchContext->getCurrentBranchId();
            abort_unless($branchId, 403, 'User belum memiliki cabang.');
            return $branchId;
        }
        if (!$request->filled('branch_id')) {
            if ($required) throw ValidationException::withMessages(['branch_id' => 'Cabang wajib dipilih.']);
            return null;
        }
        $branchId = $request->integer('branch_id');
        if (!Branch::query()->whereKey($branchId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak aktif atau tidak valid.']);
        }
        return $branchId;
    }

    private function availableBranches()
    {
        return $this->branchContext->isSuperAdmin()
            ? Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name'])
            : collect();
    }
}
