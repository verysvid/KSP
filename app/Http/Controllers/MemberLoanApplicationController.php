<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberLoanApplicationRequest;
use App\Http\Requests\UpdateMemberLoanApplicationRequest;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use App\Services\AuditLogService;
use App\Services\LoanCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberLoanApplicationController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected LoanCalculatorService $loanCalculatorService
    ) {
    }

    public function index(Request $request): View
    {
        $member = $this->resolveActiveMember($request);

        $query = Loan::query()
            ->with([
                'branch:id,code,name',
                'loanType:id,code,name',
            ])
            ->where('member_id', $member->id);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'loan_type_id' => ['nullable', 'integer', 'exists:loan_types,id'],
            'status' => [
                'nullable',
                'in:DRAFT,SUBMITTED,APPROVED,REJECTED,ACTIVE,PAID_OFF,CANCELLED',
            ],
        ]);

        if (!empty($validated['search'])) {
            $search = trim((string) $validated['search']);

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('loan_no', 'like', "%{$search}%")
                    ->orWhereHas('loanType', function ($loanTypeQuery) use ($search) {
                        $loanTypeQuery->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($validated['loan_type_id'])) {
            $query->where('loan_type_id', (int) $validated['loan_type_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $totalLoans = (clone $query)->count();
        $draftLoans = (clone $query)
            ->where('status', Loan::STATUS_DRAFT)
            ->count();
        $submittedLoans = (clone $query)
            ->where('status', Loan::STATUS_SUBMITTED)
            ->count();

        $loans = $query
            ->latest('application_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $loanTypes = LoanType::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('member-loan-applications.index', compact(
            'member',
            'loans',
            'loanTypes',
            'totalLoans',
            'draftLoans',
            'submittedLoans'
        ));
    }

    public function create(Request $request): View
    {
        $member = $this->resolveActiveMember($request);

        $loanTypes = LoanType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('member-loan-applications.create', compact(
            'member',
            'loanTypes'
        ));
    }

    public function store(StoreMemberLoanApplicationRequest $request): RedirectResponse
    {
        $member = $this->resolveActiveMember($request);
        $validated = $request->validated();

        $loanType = LoanType::query()
            ->whereKey($validated['loan_type_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $this->validateAgainstLoanType(
            $loanType,
            (float) $validated['principal_amount'],
            (int) $validated['tenor_months']
        );

        $loan = DB::transaction(function () use ($member, $validated, $loanType, $request) {
            $loan = Loan::create([
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'loan_type_id' => $loanType->id,
                'loan_no' => $this->generateLoanNo((int) $member->branch_id),
                'application_date' => $validated['application_date'],
                'principal_amount' => $validated['principal_amount'],
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
                "Anggota membuat draft pengajuan pinjaman {$loan->loan_no}",
                [],
                $loan->fresh()->toArray()
            );

            return $loan;
        });

        return redirect()
            ->route('member-loan-applications.show', $loan)
            ->with('success', 'Pengajuan pinjaman berhasil disimpan sebagai Draft.');
    }

    public function show(Request $request, Loan $loan): View
    {
        $member = $this->resolveActiveMember($request);
        $this->ensureLoanOwnedByMember($loan, $member);

        $loan->load([
            'branch',
            'member',
            'loanType',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'disbursedBy',
            'createdBy',
            'disbursement.cashAccount',
            'installments',
            'payments.installment',
            'payments.cashAccount',
        ]);

        return view('member-loan-applications.show', compact('loan', 'member'));
    }

    public function edit(Request $request, Loan $loan): View
    {
        $member = $this->resolveActiveMember($request);
        $this->ensureEditableLoan($loan, $member);

        $loanTypes = LoanType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('member-loan-applications.edit', compact(
            'loan',
            'member',
            'loanTypes'
        ));
    }

    public function update(
        UpdateMemberLoanApplicationRequest $request,
        Loan $loan
    ): RedirectResponse {
        $member = $this->resolveActiveMember($request);
        $this->ensureEditableLoan($loan, $member);

        $validated = $request->validated();

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
            'branch_id' => $member->branch_id,
            'member_id' => $member->id,
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
            "Anggota mengubah draft pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('member-loan-applications.show', $loan)
            ->with('success', 'Draft pengajuan pinjaman berhasil diperbarui.');
    }

    public function destroy(Request $request, Loan $loan): RedirectResponse
    {
        $member = $this->resolveActiveMember($request);
        $this->ensureEditableLoan($loan, $member);

        $oldValues = $loan->toArray();
        $loanNo = $loan->loan_no;

        $this->auditLogService->log(
            'DELETE',
            $loan,
            "Anggota menghapus draft pengajuan pinjaman {$loanNo}",
            $oldValues,
            []
        );

        $loan->delete();

        return redirect()
            ->route('member-loan-applications.index')
            ->with('success', 'Draft pengajuan pinjaman berhasil dihapus.');
    }

    public function simulation(Request $request, Loan $loan): View
    {
        $member = $this->resolveActiveMember($request);
        $this->ensureEditableLoan($loan, $member);

        $loan->load(['branch', 'member', 'loanType']);

        $schedule = $this->loanCalculatorService->buildSchedule(
            $loan,
            $loan->application_date->toDateString()
        );

        $totalPrincipal = round(array_sum(array_column($schedule, 'principal_amount')), 2);
        $totalInterest = round(array_sum(array_column($schedule, 'interest_amount')), 2);
        $totalInstallment = round(array_sum(array_column($schedule, 'installment_amount')), 2);

        return view('member-loan-applications.simulation', compact(
            'loan',
            'member',
            'schedule',
            'totalPrincipal',
            'totalInterest',
            'totalInstallment'
        ));
    }

    public function submit(Request $request, Loan $loan): RedirectResponse
    {
        $member = $this->resolveActiveMember($request);
        $this->ensureEditableLoan($loan, $member);

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
            "Anggota submit pengajuan pinjaman {$loan->loan_no}",
            $oldValues,
            $loan->fresh()->toArray()
        );

        return redirect()
            ->route('member-loan-applications.show', $loan)
            ->with('success', 'Pengajuan pinjaman berhasil disubmit untuk proses approval.');
    }

    protected function resolveActiveMember(Request $request): Member
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('Anggota'), 403);

        $member = $user->member()
            ->with('branch:id,code,name,is_active')
            ->where('member_status', 'ACTIVE')
            ->first();

        abort_unless(
            $member,
            403,
            'User Anggota belum terhubung dengan data anggota aktif.'
        );

        abort_unless(
            $user->branch_id
            && (int) $user->branch_id === (int) $member->branch_id,
            403,
            'Cabang user tidak sesuai dengan cabang data anggota.'
        );

        abort_unless(
            $member->branch?->is_active,
            403,
            'Cabang anggota tidak aktif.'
        );

        return $member;
    }

    protected function ensureLoanOwnedByMember(Loan $loan, Member $member): void
    {
        abort_unless(
            (int) $loan->member_id === (int) $member->id
            && (int) $loan->branch_id === (int) $member->branch_id,
            403
        );
    }

    protected function ensureEditableLoan(Loan $loan, Member $member): void
    {
        $this->ensureLoanOwnedByMember($loan, $member);

        abort_unless(
            $loan->status === Loan::STATUS_DRAFT,
            422,
            'Hanya pengajuan berstatus Draft yang dapat diproses.'
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
            abort(422, 'Nominal pinjaman lebih kecil dari batas minimum jenis pinjaman.');
        }

        if (
            $loanType->max_amount !== null
            && $principalAmount > (float) $loanType->max_amount
        ) {
            abort(422, 'Nominal pinjaman melebihi batas maksimum jenis pinjaman.');
        }

        if ($tenorMonths < (int) $loanType->min_tenor) {
            abort(422, 'Tenor pinjaman lebih kecil dari tenor minimum jenis pinjaman.');
        }

        if (
            $loanType->max_tenor !== null
            && $tenorMonths > (int) $loanType->max_tenor
        ) {
            abort(422, 'Tenor pinjaman melebihi tenor maksimum jenis pinjaman.');
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

        return sprintf('%s-%06d', $prefix, $lastSequence + 1);
    }
}
