<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingTransactionRequest;
use App\Models\Member;
use App\Models\SavingTransaction;
use App\Models\SavingType;
use App\Services\BranchContext;
use App\Services\SavingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingTransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SavingTransaction::class);

        $query = SavingTransaction::query()
            ->with(['member', 'savingType', 'branch', 'approver'])
            ->latest('transaction_date')
            ->latest('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('trx_no', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($m) =>
                        $m->where('name', 'like', "%{$search}%")
                          ->orWhere('member_number', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->input('status')));
        }

        if ($request->filled('saving_type_id')) {
            $query->where('saving_type_id', $request->integer('saving_type_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->input('date_to'));
        }

        $transactions = $query
            ->paginate(15)
            ->withQueryString();

        $savingTypes = SavingType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingCount = SavingTransaction::where('status', 'PENDING')->count();
        $approvedCount = SavingTransaction::where('status', 'APPROVED')->count();
        $rejectedCount = SavingTransaction::where('status', 'REJECTED')->count();

        return view('saving-transactions.index', compact(
            'transactions',
            'savingTypes',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function create(BranchContext $branchContext): View
    {
        $this->authorize('create', SavingTransaction::class);

        $members = Member::query()
            ->where('member_status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $savingTypes = SavingType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('saving-transactions.create', compact(
            'members',
            'savingTypes'
        ));
    }

    public function store(
        StoreSavingTransactionRequest $request,
        SavingService $savingService
    ): RedirectResponse {
        $this->authorize('create', SavingTransaction::class);

        $transaction = $savingService->createTransaction(
            $request->validated()
        );

        return redirect()
            ->route('saving-transactions.show', $transaction)
            ->with('success', 'Transaksi simpanan berhasil dibuat dan menunggu approval.');
    }

    public function show(
        SavingTransaction $savingTransaction,
        SavingService $savingService
    ): View {
        $this->authorize('view', $savingTransaction);

        $savingTransaction->load([
            'member',
            'savingType',
            'branch',
            'approver',
        ]);

        $approvedBalance = $savingService->getApprovedBalance(
            $savingTransaction->member_id,
            $savingTransaction->saving_type_id
        );

        return view('saving-transactions.show', compact(
            'savingTransaction',
            'approvedBalance'
        ));
    }

    public function approve(
        SavingTransaction $savingTransaction,
        SavingService $savingService
    ): RedirectResponse {
        $this->authorize('approve', $savingTransaction);

        $savingService->approve($savingTransaction);

        return back()->with(
            'success',
            'Transaksi simpanan berhasil disetujui.'
        );
    }

    public function reject(
        Request $request,
        SavingTransaction $savingTransaction,
        SavingService $savingService
    ): RedirectResponse {
        $this->authorize('reject', $savingTransaction);

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
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
}
