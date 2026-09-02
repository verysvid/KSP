<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneralLedgerRequest;
use App\Models\Account;
use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\GeneralLedgerService;
use Illuminate\View\View;

class GeneralLedgerController extends Controller
{
    public function index(
        GeneralLedgerRequest $request,
        BranchContext $branchContext,
        GeneralLedgerService $ledgerService
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
            ? ($request->filled('branch_id')
                ? $request->integer('branch_id')
                : null)
            : $branchContext->getCurrentBranchId();

        $accounts = Account::query()
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'type',
                'normal_balance',
                'is_active',
            ]);

        $selectedAccount = null;
        $ledger = null;

        if ($request->filled('account_id')) {
            $selectedAccount = Account::query()
                ->findOrFail($request->integer('account_id'));

            $ledger = $ledgerService->build(
                $selectedAccount,
                $dateFrom,
                $dateTo,
                $branchId
            );
        }

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

        return view('general-ledger.index', compact(
            'accounts',
            'selectedAccount',
            'ledger',
            'branches',
            'isSuperAdmin',
            'branchId',
            'dateFrom',
            'dateTo'
        ));
    }
}
