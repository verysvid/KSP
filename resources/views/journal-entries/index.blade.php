<x-app-layout>
    <x-slot name="title">Jurnal Umum</x-slot>

    <x-page-header
        title="Jurnal Umum"
        description="Lihat jurnal akuntansi yang terbentuk dari transaksi koperasi." />

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Jurnal</span>
                <div class="stat-icon">▣</div>
            </div>
            <div class="stat-value">{{ number_format($totalJournals, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Debit</span>
                <div class="stat-icon">D</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalDebit, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Kredit</span>
                <div class="stat-icon">K</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalCredit, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form
            method="GET"
            action="{{ route('journal-entries.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[minmax(240px,1fr)_160px_160px_190px_auto]">

            <input
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari no. jurnal, keterangan..."
                class="form-control">

            <input
                name="date_from"
                type="date"
                value="{{ request('date_from') }}"
                class="form-control">

            <input
                name="date_to"
                type="date"
                value="{{ request('date_to') }}"
                class="form-control">

            @if(auth()->user()?->hasRole('SuperAdmin'))
                <select name="branch_id" class="form-select">
                    <option value="">Semua Cabang</option>

                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected((string) request('branch_id') === (string) $branch->id)>
                            {{ $branch->code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <div class="hidden md:block"></div>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    Cari
                </button>

                <a href="{{ route('journal-entries.index') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Cabang</th>
                        <th>Keterangan</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($journals as $journal)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $journal->journal_no }}
                                </span>

                                <span class="table-secondary">
                                    {{ class_basename($journal->reference_type ?: '-') }}
                                </span>
                            </td>

                            <td>{{ $journal->journal_date?->format('d/m/Y') ?? '-' }}</td>

                            <td>
                                <span class="table-primary">
                                    {{ $journal->branch->code ?? '-' }}
                                </span>

                                <span class="table-secondary">
                                    {{ $journal->branch->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $journal->description }}
                                </span>
                            </td>

                            <td class="text-right">
                                Rp {{ number_format((float) ($journal->total_debit ?? 0), 0, ',', '.') }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format((float) ($journal->total_credit ?? 0), 0, ',', '.') }}
                            </td>

                            <td>
                                <div class="flex justify-end">
                                    <a href="{{ route('journal-entries.show', $journal) }}"
                                       class="btn btn-secondary">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada jurnal akuntansi.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($journals as $journal)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $journal->journal_no }}
                    </div>

                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $journal->description }}
                    </div>

                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $journal->journal_date?->format('d/m/Y') ?? '-' }}
                        ·
                        {{ $journal->branch->code ?? '-' }}
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-slate-500 dark:text-slate-400">Debit</div>
                            <div class="mt-1 font-semibold">
                                Rp {{ number_format((float) ($journal->total_debit ?? 0), 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-slate-500 dark:text-slate-400">Kredit</div>
                            <div class="mt-1 font-semibold">
                                Rp {{ number_format((float) ($journal->total_credit ?? 0), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('journal-entries.show', $journal) }}"
                           class="btn btn-secondary w-full">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state
                    title="Belum ada jurnal akuntansi"
                    description="Jurnal akan terbentuk otomatis dari transaksi koperasi." />
            @endforelse
        </div>

        @if($journals->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $journals->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
