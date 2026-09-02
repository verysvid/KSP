<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            auth()->user()?->can('account.view'),
            403
        );

        $query = Account::query()
            ->with('parent')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('code');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        if ($request->filled('postable')) {
            $query->where(
                'is_postable',
                $request->input('postable') === 'yes'
            );
        }

        $accounts = $query
            ->paginate(20)
            ->withQueryString();

        $totalCount = Account::count();
        $activeCount = Account::where('is_active', true)->count();
        $postableCount = Account::where('is_postable', true)->count();

        return view('accounts.index', compact(
            'accounts',
            'totalCount',
            'activeCount',
            'postableCount'
        ));
    }

    public function create(): View
    {
        abort_unless(
            auth()->user()?->can('account.create'),
            403
        );

        $parents = Account::query()
            ->where('is_postable', false)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return view('accounts.create', compact('parents'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        abort_unless(
            auth()->user()?->can('account.create'),
            403
        );

        Account::create($request->validated());

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function show(Account $account): View
    {
        abort_unless(
            auth()->user()?->can('account.view'),
            403
        );

        $account->load([
            'parent',
            'children',
        ]);

        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        abort_unless(
            auth()->user()?->can('account.edit'),
            403
        );

        $parents = Account::query()
            ->where('id', '!=', $account->id)
            ->where('is_postable', false)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact(
            'account',
            'parents'
        ));
    }

    public function update(
        UpdateAccountRequest $request,
        Account $account
    ): RedirectResponse {
        abort_unless(
            auth()->user()?->can('account.edit'),
            403
        );

        $account->update($request->validated());

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function toggleStatus(Account $account): RedirectResponse
    {
        abort_unless(
            auth()->user()?->can('account.edit'),
            403
        );

        if ($account->is_active && $account->journalLines()->exists()) {
            return back()->with(
                'error',
                'Akun yang sudah digunakan pada jurnal tidak dapat dinonaktifkan.'
            );
        }

        $account->update([
            'is_active' => !$account->is_active,
        ]);

        return back()->with(
            'success',
            $account->is_active
                ? 'Akun berhasil diaktifkan.'
                : 'Akun berhasil dinonaktifkan.'
        );
    }
}
