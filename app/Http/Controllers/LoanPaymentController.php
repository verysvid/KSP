<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanPaymentRequest;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\LoanPayment;
use App\Services\BranchContext;
use App\Services\LoanPaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanPaymentController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected LoanPaymentService $loanPaymentService
    ) {
    }

    public function index(Request $request): View
    {
		abort_unless(
			$request->user()?->can('installment.view'),
			403
		);

        $query = LoanPayment::query()
            ->with([
                'loan:id,branch_id,member_id,loan_no',
                'loan.member:id,name',
                'installment:id,loan_id,installment_no',
                'branch:id,code,name',
                'cashAccount:id,code,name',
            ]);

        if (!$this->branchContext->isSuperAdmin()) {
            $query->where(
                'branch_id',
                $this->branchContext->getCurrentBranchId()
            );
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('loan', function (Builder $loan) use ($search) {
                        $loan->where('loan_no', 'like', "%{$search}%")
                            ->orWhereHas('member', function (Builder $member) use ($search) {
                                $member->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = LoanPayment::query();

        if (!$this->branchContext->isSuperAdmin()) {
            $summaryQuery->where(
                'branch_id',
                $this->branchContext->getCurrentBranchId()
            );
        }

        $totalPayments = (clone $summaryQuery)->count();
        $totalAmount = (float) (clone $summaryQuery)->sum('total_amount');
        $totalPrincipal = (float) (clone $summaryQuery)->sum('principal_amount');

        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        return view('loan-payments.index', compact(
            'payments',
            'branches',
            'totalPayments',
            'totalAmount',
            'totalPrincipal'
        ));
    }

    public function create(
        Request $request,
        Loan $loan,
        LoanInstallment $installment
    ): View {
        abort_unless($request->user()?->can('loan.pay'), 403);

        $this->ensureLoanAccessible($loan);

        abort_unless(
            $loan->status === Loan::STATUS_ACTIVE,
            422,
            'Hanya pinjaman Active yang dapat dibayar.'
        );

        abort_unless(
            $installment->loan_id === $loan->id,
            404
        );

        abort_unless(
            $installment->status !== 'PAID',
            422,
            'Angsuran ini sudah lunas.'
        );

        $loan->load([
            'branch',
            'member',
            'loanType',
        ]);

        $cashAccounts = Account::query()
			->where('type', Account::TYPE_ASSET)
            ->where('is_cash_bank', true)
            ->where('is_active', true)
			->where('is_postable', true)
            ->orderBy('code')
            ->get();

        return view('loan-payments.create', compact(
            'loan',
            'installment',
            'cashAccounts'
        ));
    }

    public function store(
        StoreLoanPaymentRequest $request,
        Loan $loan,
        LoanInstallment $installment
    ): RedirectResponse {
		abort_unless(
			$request->user()?->can('loan.pay'),
			403
		);

        $this->ensureLoanAccessible($loan);

        $payment = $this->loanPaymentService->payInstallment(
            $loan,
            $installment,
            $request->validated(),
            $request->user()->id
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with(
                'success',
                "Pembayaran {$payment->payment_no} berhasil. Jurnal pembayaran juga telah terbentuk."
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
