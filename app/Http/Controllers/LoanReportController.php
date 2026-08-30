<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\LoanPayment;
use App\Services\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanReportController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function outstanding(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $branchId = $this->resolveBranchId($request);

        $query = Loan::query()
            ->with([
                'branch:id,code,name',
                'member:id,name',
                'loanType:id,code,name',
            ])
            ->where('status', Loan::STATUS_ACTIVE);

        $this->applyLoanBranch($query, $branchId);
        $this->applyLoanSearch($query, $request);

        if ($request->filled('loan_type_id')) {
            $query->where('loan_type_id', $request->integer('loan_type_id'));
        }

        $totalLoans = (clone $query)->count();
        $totalOutstandingPrincipal = (float) (clone $query)->sum('outstanding_principal');
        $totalOutstandingInterest = (float) (clone $query)->sum('outstanding_interest');

        $loans = $query
            ->orderBy('loan_no')
            ->paginate(15)
            ->withQueryString();

        return view('loan-reports.outstanding', array_merge(
            $this->commonData($branchId),
            compact(
                'loans',
                'totalLoans',
                'totalOutstandingPrincipal',
                'totalOutstandingInterest'
            )
        ));
    }

    public function due(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $branchId = $this->resolveBranchId($request);

        $dateFrom = $request->filled('date_from')
            ? $request->date_from
            : now()->toDateString();

        $dateTo = $request->filled('date_to')
            ? $request->date_to
            : now()->addDays(30)->toDateString();

        $query = LoanInstallment::query()
            ->with([
                'loan:id,branch_id,member_id,loan_type_id,loan_no,status',
                'loan.branch:id,code,name',
                'loan.member:id,name',
                'loan.loanType:id,code,name',
            ])
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '>=', $dateFrom)
            ->whereDate('due_date', '<=', $dateTo);

        $this->applyInstallmentBranch($query, $branchId);
        $this->applyInstallmentSearch($query, $request);

        $totalInstallments = (clone $query)->count();
        $totalPrincipal = (float) (clone $query)->sum('principal_amount');
        $totalInterest = (float) (clone $query)->sum('interest_amount');

        $installments = $query
            ->orderBy('due_date')
            ->orderBy('loan_id')
            ->orderBy('installment_no')
            ->paginate(15)
            ->withQueryString();

        return view('loan-reports.due', array_merge(
            $this->commonData($branchId),
            compact(
                'installments',
                'dateFrom',
                'dateTo',
                'totalInstallments',
                'totalPrincipal',
                'totalInterest'
            )
        ));
    }

    public function overdue(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $branchId = $this->resolveBranchId($request);
        $today = now()->startOfDay();

        $query = LoanInstallment::query()
            ->with([
                'loan:id,branch_id,member_id,loan_type_id,loan_no,status',
                'loan.branch:id,code,name',
                'loan.member:id,name',
                'loan.loanType:id,code,name,penalty_type',
            ])
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '<', $today->toDateString());

        $this->applyInstallmentBranch($query, $branchId);
        $this->applyInstallmentSearch($query, $request);

        if ($request->filled('aging')) {
            $aging = (string) $request->aging;

            if ($aging === '1-30') {
                $query->whereDate('due_date', '>=', $today->copy()->subDays(30)->toDateString());
            } elseif ($aging === '31-60') {
                $query
                    ->whereDate('due_date', '>=', $today->copy()->subDays(60)->toDateString())
                    ->whereDate('due_date', '<=', $today->copy()->subDays(31)->toDateString());
            } elseif ($aging === '61-90') {
                $query
                    ->whereDate('due_date', '>=', $today->copy()->subDays(90)->toDateString())
                    ->whereDate('due_date', '<=', $today->copy()->subDays(61)->toDateString());
            } elseif ($aging === '>90') {
                $query->whereDate('due_date', '<=', $today->copy()->subDays(91)->toDateString());
            }
        }

        $totalInstallments = (clone $query)->count();

        $totalPrincipalDue =
            (float) (clone $query)->sum('principal_amount')
            - (float) (clone $query)->sum('principal_paid');

        $totalInterestDue =
            (float) (clone $query)->sum('interest_amount')
            - (float) (clone $query)->sum('interest_paid');

        $totalPenaltyDue =
            (float) (clone $query)->sum('penalty_amount')
            - (float) (clone $query)->sum('penalty_paid');

        $installments = $query
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        return view('loan-reports.overdue', array_merge(
            $this->commonData($branchId),
            compact(
                'installments',
                'today',
                'totalInstallments',
                'totalPrincipalDue',
                'totalInterestDue',
                'totalPenaltyDue'
            )
        ));
    }

    public function payments(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $branchId = $this->resolveBranchId($request);

        $query = LoanPayment::query()
            ->with([
                'branch:id,code,name',
                'loan:id,branch_id,member_id,loan_no',
                'loan.member:id,name',
                'installment:id,loan_id,installment_no',
                'cashAccount:id,code,name',
            ]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
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

        $totalPayments = (clone $query)->count();
        $totalPrincipal = (float) (clone $query)->sum('principal_amount');
        $totalInterest = (float) (clone $query)->sum('interest_amount');
        $totalPenalty = (float) (clone $query)->sum('penalty_amount');
        $totalAmount = (float) (clone $query)->sum('total_amount');

        $payments = $query
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('loan-reports.payments', array_merge(
            $this->commonData($branchId),
            compact(
                'payments',
                'totalPayments',
                'totalPrincipal',
                'totalInterest',
                'totalPenalty',
                'totalAmount'
            )
        ));
    }

    protected function resolveBranchId(Request $request): ?int
    {
        if ($this->branchContext->isSuperAdmin()) {
            return $request->filled('branch_id')
                ? $request->integer('branch_id')
                : null;
        }

        return $this->branchContext->getCurrentBranchId();
    }

    protected function commonData(?int $branchId): array
    {
        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        return [
            'branches' => $branches,
            'selectedBranchId' => $branchId,
        ];
    }

    protected function applyLoanBranch(Builder $query, ?int $branchId): void
    {
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
    }

    protected function applyLoanSearch(Builder $query, Request $request): void
    {
        if (!$request->filled('search')) {
            return;
        }

        $search = trim((string) $request->search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('loan_no', 'like', "%{$search}%")
                ->orWhereHas('member', function (Builder $member) use ($search) {
                    $member->where('name', 'like', "%{$search}%");
                });
        });
    }

    protected function applyInstallmentBranch(Builder $query, ?int $branchId): void
    {
        if (!$branchId) {
            return;
        }

        $query->whereHas('loan', function (Builder $loan) use ($branchId) {
            $loan->where('branch_id', $branchId);
        });
    }

    protected function applyInstallmentSearch(Builder $query, Request $request): void
    {
        if (!$request->filled('search')) {
            return;
        }

        $search = trim((string) $request->search);

        $query->whereHas('loan', function (Builder $loan) use ($search) {
            $loan->where('loan_no', 'like', "%{$search}%")
                ->orWhereHas('member', function (Builder $member) use ($search) {
                    $member->where('name', 'like', "%{$search}%");
                });
        });
    }
}
