<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectLoanEarlyRepaymentRequest;
use App\Http\Requests\StoreLoanEarlyRepaymentRequest;
use App\Models\Loan;
use App\Models\LoanEarlyRepayment;
use App\Services\BranchContext;
use App\Services\LoanEarlyRepaymentGuardService;
use App\Services\LoanEarlyRepaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LoanEarlyRepaymentController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected LoanEarlyRepaymentGuardService $guardService,
        protected LoanEarlyRepaymentService $earlyRepaymentService,
    ) {
    }

    public function create(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('early-repayment.create'), 403);
        $this->ensureLoanAccessible($request, $loan);
        $this->guardService->ensureEligible($loan);

        $loan->load(['branch', 'member', 'loanType', 'installments', 'payments']);
        $snapshot = $this->earlyRepaymentService->buildSnapshot($loan);
        $notes = $this->earlyRepaymentService->buildNotes($loan);
        $bank = $this->earlyRepaymentService->settingsForRepayment();

        return view('loan-early-repayments.create', [
            'loan' => $loan,
            'snapshot' => $snapshot,
            'notes' => $notes,
            'setting' => $bank['setting'],
            'bankAccount' => $bank['bank_account'],
        ]);
    }

    public function store(StoreLoanEarlyRepaymentRequest $request, Loan $loan): RedirectResponse
    {
        $this->ensureLoanAccessible($request, $loan);

        $repayment = $this->earlyRepaymentService->submit(
            $loan,
            $request->file('payment_proof'),
            (int) $request->user()->id
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                "Pengajuan Pelunasan Dini {$repayment->repayment_no} berhasil dikirim. Menunggu verifikasi/approval Pengurus."
            );
    }

    public function approve(
        Request $request,
        Loan $loan,
        LoanEarlyRepayment $earlyRepayment
    ): RedirectResponse {
        abort_unless($request->user()?->can('early-repayment.approve'), 403);
        $this->ensureLoanAccessible($request, $loan);
        $this->ensureRepaymentBelongsToLoan($earlyRepayment, $loan);

        $repayment = $this->earlyRepaymentService->approve(
            $earlyRepayment,
            (int) $request->user()->id
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                "Pelunasan Dini {$repayment->repayment_no} berhasil di-approve. Pinjaman telah berstatus PAID_OFF dan jurnal telah terbentuk."
            );
    }

    public function reject(
        RejectLoanEarlyRepaymentRequest $request,
        Loan $loan,
        LoanEarlyRepayment $earlyRepayment
    ): RedirectResponse {
        $this->ensureLoanAccessible($request, $loan);
        $this->ensureRepaymentBelongsToLoan($earlyRepayment, $loan);

        $repayment = $this->earlyRepaymentService->reject(
            $earlyRepayment,
            $request->validated('rejection_reason'),
            (int) $request->user()->id
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                "Pengajuan Pelunasan Dini {$repayment->repayment_no} telah ditolak. Pinjaman kembali dapat diproses normal."
            );
    }

    public function proof(
        Request $request,
        Loan $loan,
        LoanEarlyRepayment $earlyRepayment
    ) {
        abort_unless($request->user()?->can('early-repayment.view'), 403);
        $this->ensureLoanAccessible($request, $loan);
        $this->ensureRepaymentBelongsToLoan($earlyRepayment, $loan);

        abort_unless(
            $earlyRepayment->payment_proof_path
            && Storage::disk('local')->exists($earlyRepayment->payment_proof_path),
            404,
            'Bukti pelunasan tidak ditemukan.'
        );

        return Storage::disk('local')->response(
            $earlyRepayment->payment_proof_path,
            basename($earlyRepayment->payment_proof_path),
            ['Cache-Control' => 'private, max-age=0, no-store']
        );
    }

    protected function ensureRepaymentBelongsToLoan(
        LoanEarlyRepayment $earlyRepayment,
        Loan $loan
    ): void {
        abort_unless(
            (int) $earlyRepayment->loan_id === (int) $loan->id,
            404
        );
    }

    protected function ensureLoanAccessible(Request $request, Loan $loan): void
    {
        $user = $request->user();

        if ($user?->hasRole('Anggota')) {
            $member = $user->member()
                ->with('branch:id,code,name,is_active')
                ->where('member_status', 'ACTIVE')
                ->first();

            abort_unless($member, 403, 'User Anggota belum terhubung dengan data anggota aktif.');
            abort_unless(
                $user->branch_id
                && (int) $user->branch_id === (int) $member->branch_id,
                403,
                'Cabang user tidak sesuai dengan cabang data anggota.'
            );
            abort_unless($member->branch?->is_active, 403, 'Cabang anggota tidak aktif.');
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

        abort_unless(
            $branchId !== null
            && (int) $loan->branch_id === (int) $branchId,
            403
        );
    }
}
