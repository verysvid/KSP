<x-app-layout>
    <x-slot name="title">Laporan Pinjaman</x-slot>

    <x-page-header
        title="Laporan Tunggakan"
        description="Daftar angsuran yang telah melewati jatuh tempo dan belum lunas.">

        <x-slot name="actions">
            @if(Route::has('loans.overdue.refresh'))
                <form method="POST"
                      action="{{ route('loans.overdue.refresh') }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="btn btn-secondary">
                        Refresh Overdue
                    </button>
                </form>
            @endif
        </x-slot>
    </x-page-header>

    @include('loan-reports._tabs')

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jumlah Tunggakan</span>
                <div class="stat-icon">!</div>
            </div>
            <div class="stat-value">{{ number_format($totalInstallments, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pokok Tertunggak</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalPrincipalDue, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Bunga Tertunggak</span>
                <div class="stat-icon">B</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalInterestDue, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Denda Tertunggak</span>
                <div class="stat-icon">D</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalPenaltyDue, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form method="GET"
              action="{{ route('loan-reports.overdue') }}"
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
                <label for="aging" class="form-label">Aging</label>
                <select id="aging"
                        name="aging"
                        class="form-select">
                    <option value="">Semua Tunggakan</option>
                    <option value="1-30" @selected(request('aging') === '1-30')>1 - 30 Hari</option>
                    <option value="31-60" @selected(request('aging') === '31-60')>31 - 60 Hari</option>
                    <option value="61-90" @selected(request('aging') === '61-90')>61 - 90 Hari</option>
                    <option value=">90" @selected(request('aging') === '>90')>&gt; 90 Hari</option>
                </select>
            </div>

            @include('loan-reports._branch_filter')

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('loan-reports.overdue') }}"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Pinjaman</th>
                        <th>Angsuran</th>
                        <th>Jatuh Tempo</th>
                        <th>Hari Terlambat</th>
                        <th>Penalty</th>
                        <th class="text-right">Sisa Tagihan</th>
                        <th>Cabang</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($installments as $installment)
                        @php
                            $daysLate = $installment->due_date
                                ? $installment->due_date->copy()->startOfDay()->diffInDays($today)
                                : 0;

                            $remainingAmount =
                                ((float) $installment->principal_amount - (float) $installment->principal_paid)
                                + ((float) $installment->interest_amount - (float) $installment->interest_paid)
                                + ((float) $installment->penalty_amount - (float) $installment->penalty_paid);
                        @endphp

                        <tr>
                            <td>{{ $installment->loan->member->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('loans.show', $installment->loan_id) }}"
                                   class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $installment->loan->loan_no ?? '-' }}
                                </a>
                            </td>
                            <td>#{{ $installment->installment_no }}</td>
                            <td>{{ $installment->due_date?->format('d/m/Y') }}</td>
                            <td>
                                <strong class="text-red-600 dark:text-red-400">
                                    {{ $daysLate }} hari
                                </strong>
                            </td>
                            <td>
                                {{ $installment->loan->loanType->penalty_type ?? 'NONE' }}
                            </td>
                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>{{ $installment->loan->branch->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                Tidak ada tunggakan.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($installments as $installment)
                @php
                    $daysLate = $installment->due_date
                        ? $installment->due_date->copy()->startOfDay()->diffInDays($today)
                        : 0;

                    $remainingAmount =
                        ((float) $installment->principal_amount - (float) $installment->principal_paid)
                        + ((float) $installment->interest_amount - (float) $installment->interest_paid)
                        + ((float) $installment->penalty_amount - (float) $installment->penalty_paid);
                @endphp

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ $installment->loan->member->name ?? '-' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $installment->loan->loan_no ?? '-' }}
                        · Angsuran #{{ $installment->installment_no }}
                    </div>
                    <div class="mt-3 font-semibold text-red-600 dark:text-red-400">
                        {{ $daysLate }} hari terlambat
                    </div>
                    <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada tunggakan.
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
