<?php

namespace App\Http\Controllers;

use App\Http\Requests\BalanceSheetRequest;
use App\Models\Branch;
use App\Services\BalanceSheetService;
use App\Services\BranchContext;
use Illuminate\View\View;

class BalanceSheetController extends Controller
{
    public function index(
        BalanceSheetRequest $request,
        BranchContext $branchContext,
        BalanceSheetService $balanceSheetService
    ): View {
		abort_unless(
			$request->user()?->can('accounting.view'),
			403
		);

        $asOfDate = $request->input(
            'as_of_date',
            now()->toDateString()
        );

        $isSuperAdmin =
            $branchContext->isSuperAdmin();

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

        $balanceSheet = $balanceSheetService->build(
            $asOfDate,
            $branchId
        );

        return view('balance-sheet.index', compact(
            'balanceSheet',
            'branches',
            'isSuperAdmin',
            'branchId',
            'asOfDate'
        ));
    }
}
