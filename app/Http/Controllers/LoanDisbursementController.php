<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisburseLoanRequest;
use App\Models\Account;
use App\Models\Loan;
use App\Services\BranchContext;
use App\Services\LoanDisbursementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanDisbursementController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected LoanDisbursementService $loanDisbursementService
    ) {
    }

    public function create(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.disburse'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_APPROVED,
            422,
            'Hanya pinjaman berstatus Approved yang dapat dicairkan.'
        );

        $loan->load(['branch', 'member', 'loanType']);

        $cashAccounts = Account::query()
			->where('type', Account::TYPE_ASSET)
            ->where('is_cash_bank', true)
            ->where('is_active', true)
			->where('is_postable', true)
            ->orderBy('code')
            ->get();

        return view('loan-disbursements.create', compact(
            'loan',
            'cashAccounts'
        ));
    }

    public function store(
        DisburseLoanRequest $request,
        Loan $loan
    ): RedirectResponse {
		abort_unless(
			$request->user()?->can('loan.disburse'),
			403
		);

        $this->ensureLoanAccessible($loan);

        $this->loanDisbursementService->disburse(
            $loan,
            $request->validated(),
            $request->user()->id
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                'Pinjaman berhasil dicairkan, jurnal terbentuk, dan jadwal angsuran berhasil dibuat.'
            );
    }

	protected function ensureLoanAccessible(Loan $loan): void 
	{
		if ($this->branchContext->isSuperAdmin()) {
			return;
		}

		$branchId = $this->branchContext
			->getCurrentBranchId();

		abort_unless(
			$branchId !== null
			&& (int) $loan->branch_id === (int) $branchId,
			403
		);
	}

}
