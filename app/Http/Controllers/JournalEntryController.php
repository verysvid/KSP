<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('journal.view'), 403);

        $query = JournalEntry::query()
            ->with('branch:id,code,name')
            ->withSum('lines as total_debit', 'debit')
            ->withSum('lines as total_credit', 'credit');

        $this->applyScopeAndFilters($query, $request);

        $summaryQuery = JournalEntry::query();
        $this->applyScopeAndFilters($summaryQuery, $request);

        $totalJournals = (clone $summaryQuery)->count();

        $totalDebit = (float) JournalEntryLine::query()
            ->whereIn(
                'journal_entry_id',
                (clone $summaryQuery)->select('journal_entries.id')
            )
            ->sum('debit');

        $totalCredit = (float) JournalEntryLine::query()
            ->whereIn(
                'journal_entry_id',
                (clone $summaryQuery)->select('journal_entries.id')
            )
            ->sum('credit');

        $journals = $query
            ->latest('journal_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $branches = collect();

        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);
        }

        return view('journal-entries.index', compact(
            'journals',
            'branches',
            'totalJournals',
            'totalDebit',
            'totalCredit'
        ));
    }

    public function show(Request $request, JournalEntry $journalEntry): View
    {
        abort_unless($request->user()?->can('journal.view'), 403);

        if (!$this->branchContext->isSuperAdmin()) {
            abort_unless(
                $journalEntry->branch_id === $this->branchContext->getCurrentBranchId(),
                403
            );
        }

        $journalEntry->load([
            'branch',
            'createdBy',
            'lines.account',
        ]);

        return view('journal-entries.show', compact('journalEntry'));
    }

    protected function applyScopeAndFilters(
        Builder $query,
        Request $request
    ): void {
        if (!$this->branchContext->isSuperAdmin()) {
            $query->where(
                'branch_id',
                $this->branchContext->getCurrentBranchId()
            );
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $q) use ($search) {
                $q->where('journal_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhereHas('branch', function (Builder $branch) use ($search) {
                        $branch->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('journal_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('journal_date', '<=', $request->date_to);
        }
    }
}
