<x-app-layout>
    <x-slot name="title">Laporan Pinjaman</x-slot>

    <x-page-header
        title="Riwayat Pembayaran Pinjaman"
        description="Laporan penerimaan pembayaran angsuran pinjaman." />

    @include('loan-reports._tabs')

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jumlah Pembayaran</span>
                <div class="stat-icon">#</div>
            </div>
            <div class="stat-value">{{ number_format($totalPayments, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pokok Diterima</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalPrincipal, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Bunga Diterima</span>
                <div class="stat-icon">B</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalInterest, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Diterima</span>
                <div class="stat-icon">Rp</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalAmount, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Denda:
                Rp {{ number_format($totalPenalty, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form method="GET"
              action="{{ route('loan-reports.payments') }}"
              class="mb-5 flex flex-col gap-3 md:flex-row md:items-end">

            <div class="min-w-0 flex-1">
                <label for="search" class="form-label">Pencarian</label>
                <input id="search"
                       name="search"
                       type="search"
                       value="{{ request('search') }}"
                       placeholder="Pembayaran / pinjaman / anggota"
                       class="form-control">
            </div>

            <div>
                <label for="date_from" class="form-label">Dari</label>
                <input id="date_from"
                       name="date_from"
                       type="date"
                       value="{{ request('date_from') }}"
                       class="form-control">
            </div>

            <div>
                <label for="date_to" class="form-label">Sampai</label>
                <input id="date_to"
                       name="date_to"
                       type="date"
                       value="{{ request('date_to') }}"
                       class="form-control">
            </div>

            @include('loan-reports._branch_filter')

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('loan-reports.payments') }}"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Pinjaman / Anggota</th>
                        <th>Angsuran</th>
                        <th class="text-right">Pokok</th>
                        <th class="text-right">Bunga</th>
                        <th class="text-right">Denda</th>
                        <th class="text-right">Total</th>
                        <th>Kas / Bank</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $payment->payment_no }}
                                </span>
                                <span class="table-secondary">
                                    {{ $payment->reference_no ?: '-' }}
                                </span>
                            </td>
                            <td>{{ $payment->payment_date?->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('loans.show', $payment->loan_id) }}"
                                   class="table-primary">
                                    {{ $payment->loan->loan_no ?? '-' }}
                                </a>
                                <span class="table-secondary">
                                    {{ $payment->loan->member->name ?? '-' }}
                                </span>
                            </td>
                            <td>#{{ $payment->installment->installment_no ?? '-' }}</td>
                            <td class="text-right">
                                Rp {{ number_format((float) $payment->principal_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                Rp {{ number_format((float) $payment->interest_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                Rp {{ number_format((float) $payment->penalty_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                {{ $payment->cashAccount->code ?? '-' }}
                                -
                                {{ $payment->cashAccount->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                Tidak ada pembayaran pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($payments as $payment)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $payment->payment_no }}
                    </div>
                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $payment->loan->member->name ?? '-' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $payment->loan->loan_no ?? '-' }}
                        · Angsuran #{{ $payment->installment->installment_no ?? '-' }}
                    </div>
                    <div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada pembayaran pada filter ini.
                </div>
            @endforelse
        </div>

        @if($payments->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $payments->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
