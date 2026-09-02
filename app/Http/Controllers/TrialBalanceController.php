<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrialBalanceRequest;
use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\TrialBalanceService;
use Illuminate\View\View;

class TrialBalanceController extends Controller
{
    public function index(
        TrialBalanceRequest $request,
        BranchContext $branchContext,
        TrialBalanceService $trialBalanceService
    ): View {
		abort_unless(
			$request->user()?->can('accounting.view'),
			403
		);

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->toDateString()
        );

        $dateTo = $request->input(
            'date_to',
            now()->toDateString()
        );

        $isSuperAdmin = $branchContext->isSuperAdmin();

        $branchId = $isSuperAdmin
            ? (
                $request->filled('branch_id')
                    ? $request->integer('branch_id')
                    : null
            )
            : $branchContext->getCurrentBranchId();

        $branches = collect();

        if ($isSuperAdmin) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                ]);
        }

        $trialBalance = $trialBalanceService->build(
            $dateFrom,
            $dateTo,
            $branchId
        );

        return view('trial-balance.index', compact(
            'trialBalance',
            'branches',
            'isSuperAdmin',
            'branchId',
            'dateFrom',
            'dateTo'
        ));
    }
}
