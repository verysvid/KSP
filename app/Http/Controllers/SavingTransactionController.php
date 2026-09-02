<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveSavingTransactionRequest;
use App\Http\Requests\StoreSavingTransactionRequest;
use App\Models\Account;
use App\Models\Member;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use App\Services\BranchContext;
use App\Services\SavingJournalService;
use App\Services\SavingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SavingTransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize(
            'viewAny',
            SavingTransaction::class
        );

        $query = SavingTransaction::query()
            ->with([
                'member',
                'savingType',
                'branch',
                'approver',
            ])
            ->latest('transaction_date')
            ->latest('id');

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        if ($branchId !== null) {
            $query->where(
                'branch_id',
                $branchId
            );
        }

        if (
            $search = trim(
                (string) $request->input('search')
            )
        ) {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'trx_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'member',
                    fn ($memberQuery) =>
                        $memberQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'member_number',
                                'like',
                                "%{$search}%"
                            )
                );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                strtoupper(
                    (string) $request->input('status')
                )
            );
        }

        if ($request->filled('saving_type_id')) {
            $query->where(
                'saving_type_id',
                $request->integer('saving_type_id')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->input('date_to')
            );
        }

        $transactions = $query
            ->paginate(15)
            ->withQueryString();

        $savingTypes = SavingType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $statsQuery = SavingTransaction::query();

        if ($branchId !== null) {
            $statsQuery->where(
                'branch_id',
                $branchId
            );
        }

        $pendingCount = (clone $statsQuery)
            ->where('status', 'PENDING')
            ->count();

        $approvedCount = (clone $statsQuery)
            ->where('status', 'APPROVED')
            ->count();

        $rejectedCount = (clone $statsQuery)
            ->where('status', 'REJECTED')
            ->count();

        return view(
            'saving-transactions.index',
            compact(
                'transactions',
                'savingTypes',
                'pendingCount',
                'approvedCount',
                'rejectedCount'
            )
        );
    }

    public function create(): View
    {
        $this->authorize(
            'create',
            SavingTransaction::class
        );

        $membersQuery = Member::query()
            ->where('member_status', 'ACTIVE');

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        if ($branchId !== null) {
            $membersQuery->where(
                'branch_id',
                $branchId
            );
        }

        $members = $membersQuery
            ->orderBy('name')
            ->get();

        $savingTypes = SavingType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'saving-transactions.create',
            compact(
                'members',
                'savingTypes'
            )
        );
    }

    public function store(
        StoreSavingTransactionRequest $request,
        SavingService $savingService
    ): RedirectResponse {
        $this->authorize(
            'create',
            SavingTransaction::class
        );

        $data = $request->validated();

        if (! $this->branchContext->isSuperAdmin()) {
            $branchId = $this->branchContext
                ->getCurrentBranchId();

            abort_unless(
                $branchId !== null,
                403
            );

            $memberExists = Member::query()
                ->whereKey($data['member_id'])
                ->where(
                    'branch_id',
                    $branchId
                )
                ->exists();

            abort_unless(
                $memberExists,
                403
            );
        }

        $transaction = $savingService
            ->createTransaction($data);

        return redirect()
            ->route(
                'saving-transactions.show',
                $transaction
            )
            ->with(
                'success',
                'Transaksi simpanan berhasil dibuat dan menunggu approval.'
            );
    }

    public function show(
        SavingTransaction $savingTransaction,
        SavingService $savingService
    ): View {
        $this->authorize(
            'view',
            $savingTransaction
        );

        $this->ensureTransactionAccess(
            $savingTransaction
        );

        $savingTransaction->load([
            'member',
            'savingType.liabilityAccount',
            'branch',
            'approver',
            'cashAccount',
            'journalEntry',
        ]);

        $approvedBalance = $savingService
            ->getApprovedBalance(
                $savingTransaction->member_id,
                $savingTransaction->saving_type_id
            );

        $cashAccounts = Account::query()
            ->where(
                'type',
                Account::TYPE_ASSET
            )
            ->where(
                'is_cash_bank',
                true
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                'is_postable',
                true
            )
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return view(
            'saving-transactions.show',
            compact(
                'savingTransaction',
                'approvedBalance',
                'cashAccounts'
            )
        );
    }

    public function approve(
        ApproveSavingTransactionRequest $request,
        SavingTransaction $savingTransaction,
        SavingService $savingService,
        SavingJournalService $savingJournalService
    ): RedirectResponse {
        $this->authorize(
            'approve',
            $savingTransaction
        );

        $this->ensureTransactionAccess(
            $savingTransaction
        );

        $validated = $request->validated();

        DB::transaction(function () use (
            $savingTransaction,
            $savingService,
            $savingJournalService,
            $validated
        ) {
            $savingService->approve(
                $savingTransaction
            );

            $savingJournalService->post(
                transaction: $savingTransaction,
                cashAccountId:
                    (int) $validated['cash_account_id'],
                userId: auth()->id()
            );
        });

        return back()->with(
            'success',
            'Transaksi simpanan berhasil disetujui dan jurnal berhasil dibuat.'
        );
    }

    public function reject(
        Request $request,
        SavingTransaction $savingTransaction,
        SavingService $savingService
    ): RedirectResponse {
        $this->authorize(
            'reject',
            $savingTransaction
        );

        $this->ensureTransactionAccess(
            $savingTransaction
        );

        $validated = $request->validate([
            'reject_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $savingService->reject(
            $savingTransaction,
            $validated['reject_reason']
        );

        return back()->with(
            'success',
            'Transaksi simpanan berhasil ditolak.'
        );
    }

    private function ensureTransactionAccess(
        SavingTransaction $savingTransaction
    ): void {
        if ($this->branchContext->isSuperAdmin()) {
            return;
        }

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        abort_unless(
            $branchId !== null
            && (int) $savingTransaction->branch_id
                === (int) $branchId,
            403
        );
    }
}