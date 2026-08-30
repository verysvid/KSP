<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\BranchContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanDashboardController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $today = now()->startOfDay();
        $nextSevenDays = $today->copy()->addDays(7);

        $selectedBranchId = null;

        if ($this->branchContext->isSuperAdmin()) {
            $selectedBranchId = $request->filled('branch_id')
                ? $request->integer('branch_id')
                : null;
        } else {
            $selectedBranchId = $this->branchContext->getCurrentBranchId();
        }

        $loanScope = Loan::query();

        if ($selectedBranchId) {
            $loanScope->where('branch_id', $selectedBranchId);
        }

        $activeLoans = (clone $loanScope)
            ->where('status', Loan::STATUS_ACTIVE)
            ->count();

        $paidOffLoans = (clone $loanScope)
            ->where('status', Loan::STATUS_PAID_OFF)
            ->count();

        $outstandingPrincipal = (float) (clone $loanScope)
            ->where('status', Loan::STATUS_ACTIVE)
            ->sum('outstanding_principal');

        $outstandingInterest = (float) (clone $loanScope)
            ->where('status', Loan::STATUS_ACTIVE)
            ->sum('outstanding_interest');

        $installmentScope = LoanInstallment::query()
            ->whereHas('loan', function (Builder $query) use ($selectedBranchId) {
                $query->whereIn('status', [
                    Loan::STATUS_ACTIVE,
                    Loan::STATUS_PAID_OFF,
                ]);

                if ($selectedBranchId) {
                    $query->where('branch_id', $selectedBranchId);
                }
            });

        $overdueScope = (clone $installmentScope)
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '<', $today->toDateString());

        $overdueInstallments = (clone $overdueScope)->count();

		$overdueAmount = (float) (clone $overdueScope)
			->selectRaw(
				'COALESCE(SUM((principal_amount - principal_paid) + (interest_amount - interest_paid) + (penalty_amount - penalty_paid)), 0) AS overdue_total'
			)
			->value('overdue_total');
	
        $dueNextSevenDays = (clone $installmentScope)
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '>=', $today->toDateString())
            ->whereDate('due_date', '<=', $nextSevenDays->toDateString())
            ->count();

        $aging = [
            'current' => (clone $installmentScope)
                ->where('status', '!=', 'PAID')
                ->whereDate('due_date', '>=', $today->toDateString())
                ->count(),
            '1_30' => $this->countAgingBucket(clone $installmentScope, 1, 30, $today),
            '31_60' => $this->countAgingBucket(clone $installmentScope, 31, 60, $today),
            '61_90' => $this->countAgingBucket(clone $installmentScope, 61, 90, $today),
            'over_90' => (clone $installmentScope)
                ->where('status', '!=', 'PAID')
                ->whereDate('due_date', '<=', $today->copy()->subDays(91)->toDateString())
                ->count(),
        ];

        $overdueList = (clone $overdueScope)
            ->with([
                'loan:id,branch_id,member_id,loan_type_id,loan_no,status',
                'loan.member:id,name',
                'loan.branch:id,code,name',
                'loan.loanType:id,code,name,penalty_type',
            ])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $upcomingList = (clone $installmentScope)
            ->with([
                'loan:id,branch_id,member_id,loan_type_id,loan_no,status',
                'loan.member:id,name',
                'loan.branch:id,code,name',
            ])
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '>=', $today->toDateString())
            ->whereDate('due_date', '<=', $nextSevenDays->toDateString())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        return view('loan-dashboard.index', compact(
            'activeLoans',
            'paidOffLoans',
            'outstandingPrincipal',
            'outstandingInterest',
            'overdueInstallments',
            'overdueAmount',
            'dueNextSevenDays',
            'aging',
            'overdueList',
            'upcomingList',
            'branches',
            'selectedBranchId',
            'today'
        ));
    }

    protected function countAgingBucket(
        Builder $query,
        int $fromDays,
        int $toDays,
        Carbon $today
    ): int {
        $olderDate = $today->copy()->subDays($toDays);
        $newerDate = $today->copy()->subDays($fromDays);

        return $query
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '>=', $olderDate->toDateString())
            ->whereDate('due_date', '<=', $newerDate->toDateString())
            ->count();
    }
}
