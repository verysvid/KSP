<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectLoanRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected AuditLogService $auditLogService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $query = Loan::query()
            ->with([
                'branch:id,code,name',
                'member:id,branch_id,name',
                'loanType:id,code,name',
            ]);

        if (!$this->branchContext->isSuperAdmin()) {
            $query->where('branch_id', $this->branchContext->getCurrentBranchId());
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('loan_no', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('loanType', function ($typeQuery) use ($search) {
                        $typeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('loan_type_id')) {
            $query->where('loan_type_id', $request->integer('loan_type_id'));
        }

        $loans = $query
            ->latest('application_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $loanTypes = LoanType::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        $baseStats = Loan::query();

        if (!$this->branchContext->isSuperAdmin()) {
            $baseStats->where(
                'branch_id',
                $this->branchContext->getCurrentBranchId()
            );
        }

        $totalLoans = (clone $baseStats)->count();

        $draftLoans = (clone $baseStats)
            ->where('status', Loan::STATUS_DRAFT)
            ->count();

        $submittedLoans = (clone $baseStats)
            ->where('status', Loan::STATUS_SUBMITTED)
            ->count();

        return view('loans.index', compact(
            'loans',
            'loanTypes',
            'branches',
            'totalLoans',
            'draftLoans',
            'submittedLoans'
        ));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('loan.create'), 403);

        $loanTypes = LoanType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = collect();
        $members = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);

            if ($request->filled('branch_id')) {
                $members = Member::query()
                    ->where('branch_id', $request->integer('branch_id'))
                    ->where('member_status', 'ACTIVE')
                    ->orderBy('name')
                    ->get(['id', 'branch_id', 'name']);
            }
        } else {
            $members = Member::query()
                ->where('branch_id', $this->branchContext->getCurrentBranchId())
                ->where('member_status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'branch_id', 'name']);
        }

        return view('loans.create', compact(
            'loanTypes',
            'branches',
            'members'
        ));
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $branchId = $this->resolveBranchId($validated['branch_id'] ?? null);

        $this->ensureMemberBelongsToBranch(
            (int) $validated['member_id'],
            $branchId
        );

        $loanType = LoanType::query()
            ->whereKey($validated['loan_type_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $this->validateAgainstLoanType(
            $loanType,
            (float) $validated['principal_amount'],
            (int) $validated['tenor_months']
        );

        $loan = DB::transaction(function () use ($validated, $branchId, $loanType, $request) {
            $loan = Loan::create([
                'branch_id' => $branchId,
                'member_id' => $validated['member_id'],
                'loan_type_id' => $loanType->id,

                'loan_no' => $this->generateLoanNo($branchId),
                'application_date' => $validated['application_date'],

                'principal_amount' => $validated['principal_amount'],

                // Copy default Loan Type values to preserve historical data.
                'interest_type' => $loanType->interest_type,
                'interest_rate' => $loanType->interest_rate,

                'tenor_months' => $validated['tenor_months'],
                'due_day' => $validated['due_day'],

                'status' => Loan::STATUS_DRAFT,

                'total_principal' => $validated['principal_amount'],
                'total_interest' => 0,
                'total_installment' => 0,

                'outstanding_principal' => $validated['principal_amount'],
                'outstanding_interest' => 0,

                'notes' => $validated['notes'] ?? null,

                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->auditLogService->log(
                'CREATE',
                $loan,
                "Membuat draft pengajuan pinjaman {$loan->loan_no}",
                [],
                $loan->fresh()->toArray()
            );

            return $loan;
        });

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Pengajuan pinjaman berhasil dibuat sebagai Draft.');
    }

    public function show(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $this->ensureLoanAccessible($loan);

        $loan->load([
            'branch',
            'member',
            'loanType',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'disbursedBy',
            'createdBy',
            'updatedBy',
			'disbursement.cashAccount',
			'disbursement.journalEntry',
			'installments',
			'payments.installment',
			'payments.cashAccount',
			'payments.journalEntry',
        ]);

        return view('loans.show', compact('loan'));
    }

    public function edit(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.edit'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_DRAFT,
            422,
            'Hanya pinjaman berstatus Draft yang dapat diubah.'
        );

        $loanTypes = LoanType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        $members = Member::query()
            ->where('branch_id', $loan->branch_id)
            ->where('member_status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'branch_id', 'name']);

        return view('loans.edit', compact(
            'loan',
            'loanTypes',
            'branches',
            'members'
        ));
    }

    public function update(UpdateLoanRequest $request, Loan $loan): RedirectResponse
    {
        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_DRAFT,
            422,
            'Hanya pinjaman berstatus Draft yang dapat diubah.'
        );

        $validated = $request->validated();

        $branchId = $this->resolveBranchId(
            $validated['branch_id'] ?? $loan->branch_id
        );

        $this->ensureMemberBelongsToBranch(
            (int) $validated['member_id'],
            $branchId
        );

        $loanType = LoanType::query()
            ->whereKey($validated['loan_type_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $this->validateAgainstLoanType(
            $loanType,
            (float) $validated['principal_amount'],
            (int) $validated['tenor_months']
        );

        $oldValues = $loan->toArray();

        $loan->update([
            'branch_id' => $branchId,
            'member_id' => $validated['member_id'],
            'loan_type_id' => $loanType->id,

            'application_date' => $validated['application_date'],
            'principal_amount' => $validated['principal_amount'],

            'interest_type' => $loanType->interest_type,
            'interest_rate' => $loanType->interest_rate,

            'tenor_months' => $validated['tenor_months'],
            'due_day' => $validated['due_day'],

            'total_principal' => $validated['principal_amount'],
            'total_interest' => 0,
            'total_installment' => 0,

            'outstanding_principal' => $validated['principal_amount'],
            'outstanding_interest' => 0,

            'notes' => $validated['notes'] ?? null,

            'updated_by' => $request->user()->id,
        ]);

        $this->auditLogService->log(
            'UPDATE',
            $loan,
            "Mengubah draft pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Draft pengajuan pinjaman berhasil diperbarui.');
    }

    public function destroy(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.delete'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_DRAFT,
            422,
            'Hanya pinjaman berstatus Draft yang dapat dihapus.'
        );

        $oldValues = $loan->toArray();
        $loanNo = $loan->loan_no;

        $this->auditLogService->log(
            'DELETE',
            $loan,
            "Menghapus draft pengajuan pinjaman {$loanNo}",
            $oldValues,
            []
        );

        $loan->delete();

        return redirect()
            ->route('loans.index')
            ->with('success', 'Draft pengajuan pinjaman berhasil dihapus.');
    }

    public function submit(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.submit'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_DRAFT,
            422,
            'Hanya pinjaman berstatus Draft yang dapat diajukan.'
        );

        $oldValues = $loan->toArray();

        $loan->update([
            'status' => Loan::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'submitted_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->auditLogService->log(
            'UPDATE',
            $loan,
            "Submit pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Pengajuan pinjaman berhasil disubmit.');
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.approve'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_SUBMITTED,
            422,
            'Hanya pinjaman berstatus Submitted yang dapat disetujui.'
        );

        $oldValues = $loan->toArray();

        $loan->update([
            'status' => Loan::STATUS_APPROVED,

            'approved_at' => now(),
            'approved_by' => $request->user()->id,

            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,

            'updated_by' => $request->user()->id,
        ]);

        $this->auditLogService->log(
            'APPROVE',
            $loan,
            "Menyetujui pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                'Pengajuan pinjaman berhasil disetujui. Pinjaman siap masuk proses pencairan.'
            );
    }

    public function reject(
        RejectLoanRequest $request,
        Loan $loan
    ): RedirectResponse {
        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_SUBMITTED,
            422,
            'Hanya pinjaman berstatus Submitted yang dapat ditolak.'
        );

        $oldValues = $loan->toArray();

        $loan->update([
            'status' => Loan::STATUS_REJECTED,

            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_reason' => $request->validated('rejection_reason'),

            'approved_at' => null,
            'approved_by' => null,

            'updated_by' => $request->user()->id,
        ]);

        $this->auditLogService->log(
            'REJECT',
            $loan,
            "Menolak pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Pengajuan pinjaman berhasil ditolak.');
    }

    protected function resolveBranchId(?int $requestedBranchId): int
    {
        if ($this->branchContext->isSuperAdmin()) {
            abort_if(!$requestedBranchId, 422, 'Cabang wajib dipilih.');

            return $requestedBranchId;
        }

        $branchId = $this->branchContext->getCurrentBranchId();

        abort_if(!$branchId, 403, 'User belum memiliki cabang.');

        return $branchId;
    }

    protected function ensureLoanAccessible(Loan $loan): void
    {
        if ($this->branchContext->isSuperAdmin()) {
            return;
        }

        abort_unless(
            $loan->branch_id === $this->branchContext->getCurrentBranchId(),
            403
        );
    }

    protected function ensureMemberBelongsToBranch(int $memberId, int $branchId): void
    {
        $exists = Member::query()
            ->whereKey($memberId)
            ->where('branch_id', $branchId)
            ->where('member_status', 'ACTIVE')
            ->exists();

        abort_unless(
            $exists,
            422,
            'Anggota tidak aktif atau tidak termasuk dalam cabang yang dipilih.'
        );
    }

    protected function validateAgainstLoanType(
        LoanType $loanType,
        float $principalAmount,
        int $tenorMonths
    ): void {
        if (
            $loanType->min_amount !== null
            && $principalAmount < (float) $loanType->min_amount
        ) {
            abort(
                422,
                'Nominal pinjaman lebih kecil dari batas minimum jenis pinjaman.'
            );
        }

        if (
            $loanType->max_amount !== null
            && $principalAmount > (float) $loanType->max_amount
        ) {
            abort(
                422,
                'Nominal pinjaman melebihi batas maksimum jenis pinjaman.'
            );
        }

        if ($tenorMonths < (int) $loanType->min_tenor) {
            abort(
                422,
                'Tenor pinjaman lebih kecil dari tenor minimum jenis pinjaman.'
            );
        }

        if (
            $loanType->max_tenor !== null
            && $tenorMonths > (int) $loanType->max_tenor
        ) {
            abort(
                422,
                'Tenor pinjaman melebihi tenor maksimum jenis pinjaman.'
            );
        }
    }

    protected function generateLoanNo(int $branchId): string
    {
        $branch = Branch::query()->findOrFail($branchId);

        $prefix = 'LN-' . strtoupper($branch->code) . '-' . now()->format('Ym');

        $lastLoan = Loan::query()
            ->where('loan_no', 'like', $prefix . '-%')
            ->latest('id')
            ->first();

        $lastSequence = 0;

        if ($lastLoan) {
            $parts = explode('-', $lastLoan->loan_no);
            $lastSequence = (int) end($parts);
        }

        return sprintf(
            '%s-%06d',
            $prefix,
            $lastSequence + 1
        );
    }
}
