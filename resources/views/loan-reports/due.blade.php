<x-app-layout>
    <x-slot name="title">Laporan Pinjaman</x-slot>

    <x-page-header
        title="Laporan Jatuh Tempo"
        description="Daftar angsuran yang akan jatuh tempo pada periode tertentu." />

    @include('loan-reports._tabs')

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jumlah Angsuran</span>
                <div class="stat-icon">#</div>
            </div>
            <div class="stat-value">{{ number_format($totalInstallments, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pokok Jatuh Tempo</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalPrincipal, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Bunga Jatuh Tempo</span>
                <div class="stat-icon">B</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalInterest, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form method="GET"
              action="{{ route('loan-reports.due') }}"
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

            <div>
                <label for="date_from" class="form-label">Dari</label>
                <input id="date_from"
                       name="date_from"
                       type="date"
                       value="{{ request('date_from', $dateFrom) }}"
                       class="form-control">
            </div>

            <div>
                <label for="date_to" class="form-label">Sampai</label>
                <input id="date_to"
                       name="date_to"
                       type="date"
                       value="{{ request('date_to', $dateTo) }}"
                       class="form-control">
            </div>

            @include('loan-reports._branch_filter')

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('loan-reports.due') }}"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Jatuh Tempo</th>
                        <th>Pinjaman</th>
                        <th>Anggota</th>
                        <th>Angsuran</th>
                        <th class="text-right">Pokok</th>
                        <th class="text-right">Bunga</th>
                        <th class="text-right">Total</th>
                        <th>Cabang</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($installments as $installment)
                        <tr>
                            <td>{{ $installment->due_date?->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('loans.show', $installment->loan_id) }}"
                                   class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $installment->loan->loan_no ?? '-' }}
                                </a>
                            </td>
                            <td>{{ $installment->loan->member->name ?? '-' }}</td>
                            <td>#{{ $installment->installment_no }}</td>
                            <td class="text-right">
                                Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>{{ $installment->loan->branch->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                Tidak ada angsuran jatuh tempo pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($installments as $installment)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ $installment->loan->member->name ?? '-' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $installment->loan->loan_no ?? '-' }}
                        · Angsuran #{{ $installment->installment_no }}
                    </div>
                    <div class="mt-3 font-semibold text-slate-900 dark:text-white">
                        {{ $installment->due_date?->format('d/m/Y') }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada angsuran jatuh tempo pada periode ini.
                </div>
            @endforelse
        </div>

        @if($installments->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $installments->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
