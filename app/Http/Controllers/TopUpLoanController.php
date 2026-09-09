<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanTopUpRequest;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use App\Services\LoanTopUpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TopUpLoanController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected AuditLogService $auditLogService,
        protected LoanTopUpService $topUpService
    ) {
    }

    public function create(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.create'), 403);
        $this->ensureLoanAccessible($request, $loan);
        $this->topUpService->ensureEligible($loan);

        $loan->load(['branch', 'member', 'loanType', 'installments']);

        abort_unless($loan->loanType?->is_active, 422, 'Jenis pinjaman pada pinjaman lama sudah tidak aktif.');

        $snapshot = $this->topUpService->buildSnapshot($loan);
        $notes = $this->topUpService->buildNotes($loan);

        return view('loans.topup-form', [
            'sourceLoan' => $loan,
            'topUpLoan' => null,
            'snapshot' => $snapshot,
            'notes' => $notes,
            'formAction' => route('loans.topup.store', $loan),
            'formMethod' => 'POST',
            'submitLabel' => 'Simpan Draft TopUp',
            'pageTitle' => 'Tambah Pengajuan TopUp',
        ]);
    }

    public function store(StoreLoanTopUpRequest $request, Loan $loan): RedirectResponse
    {
        $this->ensureLoanAccessible($request, $loan);
        $validated = $request->validated();

        $topUpLoan = DB::transaction(function () use ($request, $loan, $validated) {
            $sourceLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $this->topUpService->ensureEligible($sourceLoan);
            $sourceLoan->loadMissing('loanType');

            abort_unless($sourceLoan->loanType?->is_active, 422, 'Jenis pinjaman pada pinjaman lama sudah tidak aktif.');

            $topUpAmount = round((float) $validated['topup_amount'], 2);
            $oldOutstanding = round((float) $sourceLoan->outstanding_principal, 2);
            $newPrincipal = round($topUpAmount + $oldOutstanding, 2);
            $tenor = (int) $validated['tenor_months'];

            $this->validateAgainstLoanType($sourceLoan->loanType, $newPrincipal, $tenor);

            $created = Loan::create([
                'branch_id' => $sourceLoan->branch_id,
                'member_id' => $sourceLoan->member_id,
                'loan_type_id' => $sourceLoan->loan_type_id,
                'loan_no' => $this->generateLoanNo((int) $sourceLoan->branch_id),
                'application_date' => now()->toDateString(),
                'principal_amount' => $newPrincipal,
                'interest_type' => $sourceLoan->interest_type,
                'interest_rate' => $sourceLoan->interest_rate,
                'tenor_months' => $tenor,
                'due_day' => 20,
                'status' => Loan::STATUS_DRAFT,
                'total_principal' => $newPrincipal,
                'total_interest' => 0,
                'total_installment' => 0,
                'outstanding_principal' => $newPrincipal,
                'outstanding_interest' => 0,
                'notes' => $this->topUpService->buildNotes($sourceLoan),
                'is_topup' => true,
                'topup_from_loan_id' => $sourceLoan->id,
                'old_loan_no' => $sourceLoan->loan_no,
                'topup_amount' => $topUpAmount,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->auditLogService->log(
                'CREATE',
                $created,
                "Membuat draft TopUp {$created->loan_no} dari pinjaman {$sourceLoan->loan_no}",
                [],
                $created->fresh()->toArray()
            );

            return $created;
        });

        return redirect()
            ->route('loans.show', $topUpLoan)
            ->with('success', 'Pengajuan TopUp berhasil dibuat sebagai Draft.');
    }

    public function edit(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.edit'), 403);
        $this->ensureLoanAccessible($request, $loan);
        abort_unless($loan->is_topup && $loan->status === Loan::STATUS_DRAFT, 422, 'Hanya draft TopUp yang dapat diubah.');

        $sourceLoan = Loan::query()->findOrFail($loan->topup_from_loan_id);
        $this->ensureLoanAccessible($request, $sourceLoan);
        abort_unless($sourceLoan->status === Loan::STATUS_ACTIVE, 422, 'Pinjaman sumber TopUp sudah tidak aktif.');

        $sourceLoan->load(['branch', 'member', 'loanType', 'installments']);
        $snapshot = $this->topUpService->buildSnapshot($sourceLoan);
        $notes = $this->topUpService->buildNotes($sourceLoan);

        return view('loans.topup-form', [
            'sourceLoan' => $sourceLoan,
            'topUpLoan' => $loan,
            'snapshot' => $snapshot,
            'notes' => $notes,
            'formAction' => route('loans.topup.update', $loan),
            'formMethod' => 'PUT',
            'submitLabel' => 'Simpan Perubahan TopUp',
            'pageTitle' => 'Edit Pengajuan TopUp',
        ]);
    }

    public function update(StoreLoanTopUpRequest $request, Loan $loan): RedirectResponse
    {
        $this->ensureLoanAccessible($request, $loan);
        abort_unless($loan->is_topup && $loan->status === Loan::STATUS_DRAFT, 422, 'Hanya draft TopUp yang dapat diubah.');
        $validated = $request->validated();

        DB::transaction(function () use ($request, $loan, $validated) {
            $topUpLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $sourceLoan = Loan::query()->lockForUpdate()->findOrFail($topUpLoan->topup_from_loan_id);

            abort_unless($sourceLoan->status === Loan::STATUS_ACTIVE, 422, 'Pinjaman sumber TopUp sudah tidak aktif.');
            $sourceLoan->loadMissing('loanType');

            $topUpAmount = round((float) $validated['topup_amount'], 2);
            $oldOutstanding = round((float) $sourceLoan->outstanding_principal, 2);
            $newPrincipal = round($topUpAmount + $oldOutstanding, 2);
            $tenor = (int) $validated['tenor_months'];

            $this->validateAgainstLoanType($sourceLoan->loanType, $newPrincipal, $tenor);

            $old = $topUpLoan->toArray();
            $topUpLoan->update([
                'principal_amount' => $newPrincipal,
                'tenor_months' => $tenor,
                'due_day' => 20,
                'total_principal' => $newPrincipal,
                'total_interest' => 0,
                'total_installment' => 0,
                'outstanding_principal' => $newPrincipal,
                'outstanding_interest' => 0,
                'topup_amount' => $topUpAmount,
                'old_loan_no' => $sourceLoan->loan_no,
                'notes' => $this->topUpService->buildNotes($sourceLoan),
                'updated_by' => $request->user()->id,
            ]);

            $this->auditLogService->log(
                'UPDATE',
                $topUpLoan,
                "Mengubah draft TopUp {$topUpLoan->loan_no}",
                $old,
                $topUpLoan->fresh()->toArray()
            );
        });

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Draft TopUp berhasil diperbarui.');
    }

    protected function ensureLoanAccessible(Request $request, Loan $loan): void
    {
        $user = $request->user();

        if ($user?->hasRole('Anggota')) {
            $member = $user->member()->where('member_status', 'ACTIVE')->first();
            abort_unless($member, 403, 'User Anggota belum terhubung dengan data anggota aktif.');
            abort_unless(
                (int) $loan->member_id === (int) $member->id
                && (int) $loan->branch_id === (int) $member->branch_id,
                403
            );
            return;
        }

        if ($this->branchContext->isSuperAdmin()) {
            return;
        }

        $branchId = $this->branchContext->getCurrentBranchId();
        abort_unless($branchId !== null && (int) $loan->branch_id === (int) $branchId, 403);
    }

    protected function validateAgainstLoanType(LoanType $loanType, float $principalAmount, int $tenorMonths): void
    {
        if ($loanType->min_amount !== null && $principalAmount < (float) $loanType->min_amount) {
            throw ValidationException::withMessages(['topup_amount' => 'Total pinjaman baru lebih kecil dari batas minimum jenis pinjaman.']);
        }
        if ($loanType->max_amount !== null && $principalAmount > (float) $loanType->max_amount) {
            throw ValidationException::withMessages(['topup_amount' => 'Total pinjaman baru melebihi batas maksimum jenis pinjaman.']);
        }
        if ($tenorMonths < (int) $loanType->min_tenor) {
            throw ValidationException::withMessages(['tenor_months' => 'Tenor lebih kecil dari tenor minimum jenis pinjaman.']);
        }
        if ($loanType->max_tenor !== null && $tenorMonths > (int) $loanType->max_tenor) {
            throw ValidationException::withMessages(['tenor_months' => 'Tenor melebihi tenor maksimum jenis pinjaman.']);
        }
    }

    protected function generateLoanNo(int $branchId): string
    {
        $branch = Branch::query()->findOrFail($branchId);
        $prefix = 'LN-' . strtoupper($branch->code) . '-' . now()->format('Ym');
        $last = Loan::query()->where('loan_no', 'like', $prefix . '-%')->latest('id')->first();
        $seq = 0;

        if ($last) {
            $parts = explode('-', $last->loan_no);
            $seq = (int) end($parts);
        }

        return sprintf('%s-%06d', $prefix, $seq + 1);
    }
}
