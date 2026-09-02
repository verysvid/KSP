<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanTypeRequest;
use App\Http\Requests\UpdateLoanTypeRequest;
use App\Models\LoanType;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = LoanType::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('interest_type')) {
            $query->where(
                'interest_type',
                $request->interest_type
            );
        }

        $loanTypes = $query
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('loan-types.index', compact('loanTypes'));
    }

	public function create(): View
	{
		$receivableAccounts = Account::query()
			->where('type', Account::TYPE_ASSET)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		$interestIncomeAccounts = Account::query()
			->where('type', Account::TYPE_REVENUE)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		$penaltyIncomeAccounts = Account::query()
			->where('type', Account::TYPE_REVENUE)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		return view(
			'loan-types.create',
			compact(
				'receivableAccounts',
				'interestIncomeAccounts',
				'penaltyIncomeAccounts'
			)
		);
	}

    public function store(
        StoreLoanTypeRequest $request
    ): RedirectResponse {
        LoanType::create($request->validated());

        return redirect()
            ->route('loan-types.index')
            ->with(
                'success',
                'Jenis pinjaman berhasil ditambahkan.'
            );
    }

    public function show(LoanType $loanType): View
    {
		$loanType->loadMissing([
			'receivableAccount',
			'interestIncomeAccount',
			'penaltyIncomeAccount',
		]);
        return view(
            'loan-types.show',
            compact('loanType')
        );
    }

	public function edit(LoanType $loanType): View
	{
		$receivableAccounts = Account::query()
			->where('type', Account::TYPE_ASSET)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		$interestIncomeAccounts = Account::query()
			->where('type', Account::TYPE_REVENUE)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		$penaltyIncomeAccounts = Account::query()
			->where('type', Account::TYPE_REVENUE)
			->where('is_active', true)
			->where('is_postable', true)
			->orderBy('code')
			->get();

		return view(
			'loan-types.edit',
			compact(
				'loanType',
				'receivableAccounts',
				'interestIncomeAccounts',
				'penaltyIncomeAccounts'
			)
		);
	}

    public function update(
        UpdateLoanTypeRequest $request,
        LoanType $loanType
    ): RedirectResponse {
        $loanType->update($request->validated());

        return redirect()
            ->route('loan-types.index')
            ->with(
                'success',
                'Jenis pinjaman berhasil diperbarui.'
            );
    }

    public function toggleStatus(
        LoanType $loanType
    ): RedirectResponse {
        $loanType->update([
            'is_active' => !$loanType->is_active,
        ]);

        $message = $loanType->is_active
            ? 'Jenis pinjaman berhasil diaktifkan.'
            : 'Jenis pinjaman berhasil dinonaktifkan.';

        return redirect()
            ->route('loan-types.index')
            ->with('success', $message);
    }
}