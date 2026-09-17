<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\SavingType;
use App\Models\ShuPeriod;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use App\Services\JournalService;
use App\Services\ShuCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShuController extends Controller
{
    public function index(Request $request, BranchContext $branchContext): View
    {
        $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'status' => ['nullable', Rule::in([
                ShuPeriod::STATUS_DRAFT,
                ShuPeriod::STATUS_CALCULATED,
                ShuPeriod::STATUS_FINALIZED,
                ShuPeriod::STATUS_PAID,
            ])],
        ]);

        $isSuperAdmin = $branchContext->isSuperAdmin();
        $branches = $isSuperAdmin
            ? Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name'])
            : collect();

        $query = ShuPeriod::query()->with(['branch:id,code,name', 'allocations']);

        if ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }
        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $periods = $query->orderByDesc('year')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('shu.index', compact('periods', 'branches', 'isSuperAdmin'));
    }

    public function create(BranchContext $branchContext): View
    {
        $defaultYear = now()->subYear()->year;

        $period = new ShuPeriod([
            'year' => $defaultYear,
            'start_date' => \Illuminate\Support\Carbon::create($defaultYear, 1, 1),
            'end_date' => \Illuminate\Support\Carbon::create($defaultYear, 12, 31),
            'capital_share_percentage' => 40,
            'business_share_percentage' => 60,
            'post_journal' => false,
        ]);

        $allocations = collect([
            ['name' => 'Dana Cadangan', 'percentage' => 25, 'is_member_pool' => false, 'account_id' => null],
            ['name' => 'Bagian Anggota', 'percentage' => 50, 'is_member_pool' => true, 'account_id' => null],
            ['name' => 'Dana Pendidikan', 'percentage' => 5, 'is_member_pool' => false, 'account_id' => null],
            ['name' => 'Pengurus/Pengawas', 'percentage' => 10, 'is_member_pool' => false, 'account_id' => null],
            ['name' => 'Dana Sosial', 'percentage' => 5, 'is_member_pool' => false, 'account_id' => null],
            ['name' => 'Dana Lain-lain', 'percentage' => 5, 'is_member_pool' => false, 'account_id' => null],
        ]);

        return view('shu.create', array_merge(
            $this->formOptions($branchContext),
            [
                'period' => $period,
                'allocations' => $allocations,
                'selectedSavingTypeIds' => $this->defaultSavingTypeIds(),
            ]
        ));
    }

    public function store(Request $request, BranchContext $branchContext, AuditLogService $auditLog): RedirectResponse
    {
        $data = $this->validatePeriod($request, $branchContext);

        $period = DB::transaction(function () use ($request, $data, $branchContext) {
            $branchId = $branchContext->isSuperAdmin()
                ? (int) $data['branch_id']
                : (int) $branchContext->getCurrentBranchId();

            $this->assertUniqueYear($branchId, (int) $data['year']);

            $period = ShuPeriod::create([
                'branch_id' => $branchId,
                'year' => $data['year'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'capital_share_percentage' => $data['capital_share_percentage'],
                'business_share_percentage' => $data['business_share_percentage'],
                'post_journal' => $request->boolean('post_journal'),
                'source_equity_account_id' => $data['source_equity_account_id'] ?? null,
                'status' => ShuPeriod::STATUS_DRAFT,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->syncConfiguration($period, $data);
            return $period;
        });

        $auditLog->log('CREATE', $period, "Membuat periode SHU {$period->year}");

        return redirect()->route('shu.show', $period)->with('success', 'Periode SHU berhasil dibuat.');
    }

    public function show(ShuPeriod $shu): View
    {
        $shu->load(['branch', 'yearClosing', 'allocations.account', 'savingTypes', 'sourceEquityAccount', 'journalEntry']);
        $results = $shu->results()->with('member:id,name,member_number')->paginate(50);

        return view('shu.show', ['period' => $shu, 'results' => $results]);
    }

    public function edit(ShuPeriod $shu, BranchContext $branchContext): View
    {
        abort_if($shu->isLocked(), 422, 'SHU yang sudah difinalisasi tidak dapat diedit.');
        $shu->load(['allocations', 'savingTypes']);

        return view('shu.edit', array_merge(
            $this->formOptions($branchContext),
            [
                'period' => $shu,
                'allocations' => $shu->allocations->map(fn ($item) => [
                    'name' => $item->name,
                    'percentage' => (float) $item->percentage,
                    'is_member_pool' => (bool) $item->is_member_pool,
                    'account_id' => $item->account_id,
                ]),
                'selectedSavingTypeIds' => $shu->savingTypes->pluck('id')->map(fn ($id) => (int) $id)->all(),
            ]
        ));
    }

    public function update(Request $request, ShuPeriod $shu, BranchContext $branchContext, AuditLogService $auditLog): RedirectResponse
    {
        abort_if($shu->isLocked(), 422, 'SHU yang sudah difinalisasi tidak dapat diedit.');
        $data = $this->validatePeriod($request, $branchContext, $shu);

        DB::transaction(function () use ($request, $data, $shu, $branchContext) {
            $branchId = $branchContext->isSuperAdmin()
                ? (int) $data['branch_id']
                : (int) $branchContext->getCurrentBranchId();

            $this->assertUniqueYear($branchId, (int) $data['year'], $shu->id);

            $shu->update([
                'branch_id' => $branchId,
                'year' => $data['year'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'capital_share_percentage' => $data['capital_share_percentage'],
                'business_share_percentage' => $data['business_share_percentage'],
                'post_journal' => $request->boolean('post_journal'),
                'source_equity_account_id' => $data['source_equity_account_id'] ?? null,
                'status' => ShuPeriod::STATUS_DRAFT,
                'year_closing_id' => null,
                'total_revenue' => 0,
                'total_expense' => 0,
                'net_shu' => 0,
                'calculated_at' => null,
                'updated_by' => $request->user()?->id,
            ]);

            $shu->results()->delete();
            $this->syncConfiguration($shu, $data);
        });

        $auditLog->log('UPDATE', $shu, "Mengubah parameter SHU {$shu->year}");

        return redirect()->route('shu.show', $shu)->with('success', 'Parameter SHU diperbarui. Silakan hitung ulang.');
    }

    public function calculate(ShuPeriod $shu, ShuCalculationService $service, AuditLogService $auditLog): RedirectResponse
    {
        $period = $service->calculate($shu);
        $auditLog->log('CALCULATE', $period, "Menghitung SHU {$period->year}");

        return redirect()->route('shu.show', $period)->with('success', 'Perhitungan SHU berhasil dibuat.');
    }

    public function finalize(
        Request $request,
        ShuPeriod $shu,
        JournalService $journalService,
        AuditLogService $auditLog
    ): RedirectResponse {
        if ($shu->status !== ShuPeriod::STATUS_CALCULATED) {
            return back()->with('error', 'SHU harus berstatus CALCULATED sebelum difinalisasi.');
        }

        $shu->load(['yearClosing', 'allocations', 'results']);

        if (! $shu->yearClosing || ! $shu->yearClosing->isClosed()) {
            return back()->with('error', 'Tahun buku sudah tidak CLOSED. Hitung ulang SHU setelah proses Tutup Buku selesai.');
        }

        $memberAllocation = $shu->allocations->firstWhere('is_member_pool', true);
        $memberPool = (float) ($memberAllocation?->amount ?? 0);
        $resultTotal = round((float) $shu->results->sum('total_shu'), 2);

        if (abs($memberPool - $resultTotal) > 0.01) {
            throw ValidationException::withMessages([
                'shu' => 'Total SHU anggota tidak sama dengan pool Bagian Anggota. Silakan hitung ulang sebelum finalisasi.',
            ]);
        }

        DB::transaction(function () use ($request, $shu, $journalService) {
            $journal = null;

            if ($shu->post_journal) {
                if (! $shu->source_equity_account_id) {
                    throw ValidationException::withMessages([
                        'journal' => 'Akun sumber SHU belum dipilih.',
                    ]);
                }

                $missingAccount = $shu->allocations->first(fn ($allocation) => ! $allocation->account_id);
                if ($missingAccount) {
                    throw ValidationException::withMessages([
                        'journal' => "Akun tujuan untuk alokasi {$missingAccount->name} belum dipilih.",
                    ]);
                }

                if ($shu->allocations->contains(fn ($allocation) => (int) $allocation->account_id === (int) $shu->source_equity_account_id)) {
                    throw ValidationException::withMessages([
                        'journal' => 'Akun sumber SHU tidak boleh sama dengan akun tujuan alokasi.',
                    ]);
                }

                $lines = [[
                    'account_id' => (int) $shu->source_equity_account_id,
                    'debit' => (float) $shu->net_shu,
                    'credit' => 0,
                    'description' => "Alokasi SHU Tahun Buku {$shu->year}",
                ]];

                foreach ($shu->allocations as $allocation) {
                    $lines[] = [
                        'account_id' => (int) $allocation->account_id,
                        'debit' => 0,
                        'credit' => (float) $allocation->amount,
                        'description' => $allocation->name,
                    ];
                }

                $journal = $journalService->create(
                    (int) $shu->branch_id,
                    now()->toDateString(),
                    "Alokasi SHU Tahun Buku {$shu->year}",
                    ShuPeriod::class,
                    (int) $shu->id,
                    $lines,
                    $request->user()?->id
                );
            }

            $shu->update([
                'status' => ShuPeriod::STATUS_FINALIZED,
                'finalized_at' => now(),
                'finalized_by' => $request->user()?->id,
                'journal_entry_id' => $journal?->id,
                'updated_by' => $request->user()?->id,
            ]);
        });

        $auditLog->log('FINALIZE', $shu, "Finalisasi SHU {$shu->year}");

        return redirect()->route('shu.show', $shu)->with('success', 'SHU berhasil difinalisasi dan dikunci.');
    }

    public function markPaid(Request $request, ShuPeriod $shu, AuditLogService $auditLog): RedirectResponse
    {
        if ($shu->status !== ShuPeriod::STATUS_FINALIZED) {
            return back()->with('error', 'Hanya SHU FINALIZED yang dapat ditandai PAID.');
        }

        $data = $request->validate([
            'paid_date' => ['required', 'date'],
            'payment_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $shu->update([
            'status' => ShuPeriod::STATUS_PAID,
            'paid_date' => $data['paid_date'],
            'paid_at' => now(),
            'paid_by' => $request->user()?->id,
            'payment_note' => $data['payment_note'] ?? null,
            'updated_by' => $request->user()?->id,
        ]);

        $auditLog->log('PAID', $shu, "Menandai SHU {$shu->year} telah dibayar");

        return redirect()->route('shu.show', $shu)->with('success', 'SHU telah ditandai PAID.');
    }

    private function formOptions(BranchContext $branchContext): array
    {
        return [
            'isSuperAdmin' => $branchContext->isSuperAdmin(),
            'branches' => $branchContext->isSuperAdmin()
                ? Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name'])
                : collect(),
            'savingTypes' => SavingType::query()->where('is_active', true)->orderBy('code')->get(),
            'equityAccounts' => Account::query()->active()->postable()->where('type', Account::TYPE_EQUITY)->orderBy('code')->get(),
            'postableAccounts' => Account::query()->active()->postable()->orderBy('code')->get(),
        ];
    }

    private function defaultSavingTypeIds(): array
    {
        return SavingType::query()
            ->where('is_active', true)
            ->get(['id', 'code', 'name'])
            ->filter(function (SavingType $type) {
                $value = mb_strtolower(($type->code ?? '') . ' ' . ($type->name ?? ''));
                return str_contains($value, 'pokok') || str_contains($value, 'wajib');
            })
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function validatePeriod(Request $request, BranchContext $branchContext, ?ShuPeriod $period = null): array
    {
        $rules = [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'capital_share_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'business_share_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'saving_type_ids' => ['nullable', 'array'],
            'saving_type_ids.*' => ['integer', 'exists:saving_types,id'],
            'post_journal' => ['nullable', 'boolean'],
            'source_equity_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.name' => ['required', 'string', 'max:120'],
            'allocations.*.percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'allocations.*.account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'member_allocation_index' => ['required', 'integer', 'min:0'],
        ];

        if ($branchContext->isSuperAdmin()) {
            $rules['branch_id'] = ['required', 'integer', 'exists:branches,id'];
        }

        $data = $request->validate($rules);

        if (abs(((float) $data['capital_share_percentage'] + (float) $data['business_share_percentage']) - 100) > 0.0001) {
            throw ValidationException::withMessages(['member_share' => 'Jasa Modal + Jasa Usaha harus 100%.']);
        }

        if ((float) $data['capital_share_percentage'] > 0 && empty($data['saving_type_ids'])) {
            throw ValidationException::withMessages([
                'saving_type_ids' => 'Pilih minimal satu jenis simpanan sebagai basis Jasa Modal.',
            ]);
        }

        if (abs(collect($data['allocations'])->sum(fn ($row) => (float) $row['percentage']) - 100) > 0.0001) {
            throw ValidationException::withMessages(['allocations' => 'Total alokasi SHU harus 100%.']);
        }

        if (! array_key_exists((int) $data['member_allocation_index'], $data['allocations'])) {
            throw ValidationException::withMessages(['member_allocation_index' => 'Pilih satu alokasi sebagai Bagian Anggota.']);
        }

        if ($request->boolean('post_journal')) {
            if (empty($data['source_equity_account_id'])) {
                throw ValidationException::withMessages(['source_equity_account_id' => 'Pilih akun sumber SHU untuk posting jurnal.']);
            }
            foreach ($data['allocations'] as $i => $allocation) {
                if (empty($allocation['account_id'])) {
                    throw ValidationException::withMessages([
                        "allocations.$i.account_id" => 'Akun tujuan wajib dipilih jika posting jurnal diaktifkan.',
                    ]);
                }
            }
        }

        return $data;
    }

    private function syncConfiguration(ShuPeriod $period, array $data): void
    {
        $period->savingTypes()->sync($data['saving_type_ids'] ?? []);
        $period->allocations()->delete();

        foreach ($data['allocations'] as $index => $allocation) {
            $period->allocations()->create([
                'name' => $allocation['name'],
                'percentage' => $allocation['percentage'],
                'amount' => 0,
                'is_member_pool' => (int) $data['member_allocation_index'] === (int) $index,
                'account_id' => $allocation['account_id'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function assertUniqueYear(int $branchId, int $year, ?int $ignoreId = null): void
    {
        $query = ShuPeriod::withoutGlobalScope('branch')
            ->where('branch_id', $branchId)
            ->where('year', $year);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'year' => 'Periode SHU untuk cabang dan tahun tersebut sudah ada.',
            ]);
        }
    }
}
