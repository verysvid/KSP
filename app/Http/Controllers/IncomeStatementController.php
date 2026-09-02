<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStatementRequest;
use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\IncomeStatementService;
use Illuminate\View\View;

class IncomeStatementController extends Controller
{
    public function index(
        IncomeStatementRequest $request,
        BranchContext $branchContext,
        IncomeStatementService $incomeStatementService
    ): View {
		abort_unless(
			$request->user()?->can('accounting.view'),
			403
		);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $isSuperAdmin = $branchContext->isSuperAdmin();

        $branchId = $isSuperAdmin
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $branchContext->getCurrentBranchId();

        $branches = collect();

        if ($isSuperAdmin) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        $incomeStatement = $incomeStatementService->build(
            $dateFrom,
            $dateTo,
            $branchId
        );

        return view('income-statement.index', compact(
            'incomeStatement',
            'branches',
            'isSuperAdmin',
            'branchId',
            'dateFrom',
            'dateTo'
        ));
    }
}
