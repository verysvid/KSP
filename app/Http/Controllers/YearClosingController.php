<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\YearClosing;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use App\Services\YearClosingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YearClosingController extends Controller
{
    public function index(Request $request, BranchContext $branchContext): View
    {
        $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', Rule::in([YearClosing::STATUS_CLOSED, YearClosing::STATUS_REOPENED])],
        ]);

        $isSuperAdmin = $branchContext->isSuperAdmin();
        $branches = $isSuperAdmin
            ? Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name'])
            : collect();

        $query = YearClosing::query()->with(['branch:id,code,name', 'closedBy:id,name', 'reopenedBy:id,name']);

        if ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $closings = $query->orderByDesc('year')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('year-closing.index', compact('closings', 'branches', 'isSuperAdmin'));
    }

    public function preview(Request $request, BranchContext $branchContext, YearClosingService $service): View
    {
        $rules = [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];

        if ($branchContext->isSuperAdmin()) {
            $rules['branch_id'] = ['required', 'integer', 'exists:branches,id'];
        }

        $data = $request->validate($rules);
        $branchId = $branchContext->isSuperAdmin()
            ? (int) $data['branch_id']
            : (int) $branchContext->getCurrentBranchId();

        abort_unless($branchId > 0, 403, 'User belum memiliki cabang.');

        $branch = Branch::query()->findOrFail($branchId);
        $preview = $service->preview($branchId, (int) $data['year']);
        $equityAccounts = Account::query()
            ->where('type', Account::TYPE_EQUITY)
            ->where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('year-closing.preview', compact('preview', 'branch', 'equityAccounts'));
    }

    public function show(YearClosing $yearClosing): View
    {
        $yearClosing->load(['branch', 'closedBy', 'reopenedBy', 'closingEquityAccount', 'journalEntry', 'reopenJournalEntry']);
        return view('year-closing.show', compact('yearClosing'));
    }

    public function close(
        Request $request,
        BranchContext $branchContext,
        YearClosingService $service,
        AuditLogService $auditLog
    ): RedirectResponse {
        $rules = [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'closing_equity_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'close_note' => ['nullable', 'string', 'max:2000'],
        ];

        if ($branchContext->isSuperAdmin()) {
            $rules['branch_id'] = ['required', 'integer', 'exists:branches,id'];
        }

        $data = $request->validate($rules);
        $branchId = $branchContext->isSuperAdmin()
            ? (int) $data['branch_id']
            : (int) $branchContext->getCurrentBranchId();

        abort_unless($branchId > 0, 403, 'User belum memiliki cabang.');

        $closing = $service->close(
            $branchId,
            (int) $data['year'],
            (int) $data['closing_equity_account_id'],
            $request->user()?->id,
            $data['close_note'] ?? null
        );

        $auditLog->log(
            'CLOSE',
            $closing,
            "Tutup Buku Tahun {$closing->year}",
            [],
            $closing->fresh()->toArray()
        );

        return redirect()->route('year-closing.show', $closing)
            ->with('success', "Tahun Buku {$closing->year} berhasil ditutup dan dikunci.");
    }

    public function reopen(
        Request $request,
        YearClosing $yearClosing,
        YearClosingService $service,
        AuditLogService $auditLog
    ): RedirectResponse {
        $data = $request->validate([
            'reopen_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $old = $yearClosing->toArray();
        $closing = $service->reopen(
            $yearClosing,
            $data['reopen_reason'],
            $request->user()?->id
        );

        $auditLog->log(
            'REOPEN',
            $closing,
            "Membuka kembali Tahun Buku {$closing->year}",
            $old,
            $closing->fresh()->toArray()
        );

        return redirect()->route('year-closing.show', $closing)
            ->with('success', "Tahun Buku {$closing->year} berhasil dibuka kembali. Transaksi koreksi sekarang diperbolehkan.");
    }
}
