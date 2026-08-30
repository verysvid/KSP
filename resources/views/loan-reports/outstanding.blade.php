<x-app-layout>
    <x-slot name="title">Laporan Pinjaman</x-slot>

    <x-page-header
        title="Laporan Pinjaman"
        description="Outstanding pokok dan bunga pinjaman aktif." />

    @include('loan-reports._tabs')

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pinjaman Aktif</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value">{{ number_format($totalLoans, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Outstanding Pokok</span>
                <div class="stat-icon">Rp</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalOutstandingPrincipal, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Outstanding Bunga</span>
                <div class="stat-icon">B</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalOutstandingInterest, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form method="GET"
              action="{{ route('loan-reports.outstanding') }}"
              class="mb-5 flex flex-col gap-3 md:flex-row md:items-end">

            <div class="min-w-0 flex-1">
                <label for="search" class="form-label">Pencarian</label>
                <input id="search"
                       name="search"
                       type="search"
                       value="{{ request('search') }}"
                       placeholder="No. pinjaman / nama anggota"
                       class="form-control">
            </div>

            @include('loan-reports._branch_filter')

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('loan-reports.outstanding') }}"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Pinjaman</th>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th>Cabang</th>
                        <th class="text-right">Pokok Awal</th>
                        <th class="text-right">Outstanding Pokok</th>
                        <th class="text-right">Outstanding Bunga</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>
                                <a href="{{ route('loans.show', $loan) }}"
                                   class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $loan->loan_no }}
                                </a>
                            </td>
                            <td>{{ $loan->member->name ?? '-' }}</td>
                            <td>{{ $loan->loanType->name ?? '-' }}</td>
                            <td>{{ $loan->branch->name ?? '-' }}</td>
                            <td class="text-right">
                                Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="text-right">
                                Rp {{ number_format((float) $loan->outstanding_interest, 0, ',', '.') }}
                            </td>
                            <td>{{ $loan->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                Tidak ada data outstanding pinjaman.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($loans as $loan)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ $loan->member->name ?? '-' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $loan->loan_no }} · {{ $loan->loanType->name ?? '-' }}
                    </div>
                    <div class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        Outstanding Pokok
                    </div>
                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('loans.show', $loan) }}"
                           class="btn btn-secondary w-full">
                            Detail Pinjaman
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada data outstanding pinjaman.
                </div>
            @endforelse
        </div>

        @if($loans->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $loans->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
