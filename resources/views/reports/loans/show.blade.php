<x-app-layout>
    <x-slot name="title">Detail Laporan Pinjaman</x-slot>

    <x-page-header
        title="Detail Laporan Pinjaman"
        description="{{ $loan->loan_no }} - {{ $loan->member->name ?? '-' }}">
        <x-slot name="actions">
            <a href="{{ route('reports.loans.index') }}" class="btn btn-secondary print:hidden">
                Kembali
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary print:hidden">
                Cetak
            </button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-100 text-2xl font-extrabold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($loan->loanType->code ?? 'LN', 0, 2)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $loan->member->name ?? '-' }}
                </h2>

                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ $loan->member->member_number ?? '-' }}
                </div>

                <div class="mt-3">
                    <x-status-badge :status="$loan->status" />
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 px-4 py-4 dark:bg-slate-800/60">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Nominal Pinjaman
                    </div>
                    <div class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Informasi Pinjaman"
            description="Informasi pinjaman anggota.">

            <div class="info-list">
                <div class="info-row"><span>No. Pinjaman</span><strong>{{ $loan->loan_no }}</strong></div>
                <div class="info-row"><span>No. Anggota</span><strong>{{ $loan->member->member_number ?? '-' }}</strong></div>
                <div class="info-row"><span>Anggota</span><strong>{{ $loan->member->name ?? '-' }}</strong></div>
                <div class="info-row">
                    <span>Cabang</span>
                    <strong>{{ $loan->branch->code ?? '-' }} - {{ $loan->branch->name ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Jenis Pinjaman</span>
                    <strong>{{ $loan->loanType->code ?? '-' }} - {{ $loan->loanType->name ?? '-' }}</strong>
                </div>
                <div class="info-row"><span>Tanggal Pengajuan</span><strong>{{ $loan->application_date?->format('d/m/Y') ?? '-' }}</strong></div>
                <div class="info-row"><span>Tanggal Pencairan</span><strong>{{ $loan->disbursement?->disbursement_date?->format('d/m/Y') ?? $loan->disbursed_at?->format('d/m/Y') ?? '-' }}</strong></div>
                <div class="info-row"><span>Nominal Pinjaman</span><strong>Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Metode Bunga</span><strong>{{ $loan->interest_type }}</strong></div>
                <div class="info-row"><span>Suku Bunga</span><strong>{{ number_format((float) $loan->interest_rate, 4, ',', '.') }}%</strong></div>
                <div class="info-row"><span>Tenor</span><strong>{{ $loan->tenor_months }} bulan</strong></div>
                <div class="info-row"><span>Jatuh Tempo Bulanan</span><strong>Tanggal {{ $loan->due_day }}</strong></div>
                <div class="info-row"><span>Status</span><strong><x-status-badge :status="$loan->status" /></strong></div>
                <div class="info-row"><span>Catatan</span><strong class="max-w-md">{{ $loan->notes ?: '-' }}</strong></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Nilai Pinjaman" description="Ringkasan kewajiban dan sisa pinjaman.">
            <div class="info-list">
                <div class="info-row"><span>Total Pokok</span><strong>Rp {{ number_format((float) $loan->total_principal, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Bunga</span><strong>Rp {{ number_format((float) $loan->total_interest, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Angsuran</span><strong>Rp {{ number_format((float) $loan->total_installment, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Sisa Pokok</span><strong>Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Sisa Bunga</span><strong>Rp {{ number_format((float) $loan->outstanding_interest, 0, ',', '.') }}</strong></div>
            </div>
        </x-card>

        <x-card title="Ringkasan Pembayaran" description="Akumulasi pembayaran yang sudah tercatat.">
            <div class="info-list">
                <div class="info-row"><span>Angsuran Lunas</span><strong>{{ $paidInstallments }} / {{ $loan->installments->count() }}</strong></div>
                <div class="info-row"><span>Pokok Dibayar</span><strong>Rp {{ number_format($paidPrincipal, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Bunga Dibayar</span><strong>Rp {{ number_format($paidInterest, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Denda Dibayar</span><strong>Rp {{ number_format($paidPenalty, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Dibayar</span><strong>Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Jadwal Angsuran" description="Jadwal angsuran pinjaman. Halaman laporan bersifat read-only.">
            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Angsuran</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-right">Pokok</th>
                                <th class="text-right">Bunga</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Sisa Pokok</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loan->installments as $installment)
                                <tr>
                                    <td>#{{ $installment->installment_no }}</td>
                                    <td>{{ $installment->due_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}</td>
                                    <td class="text-right"><strong>Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}</strong></td>
                                    <td class="text-right">Rp {{ number_format((float) $installment->ending_principal, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$installment->status" /></td>
                                    <td>
                                        @if($installment->status === 'PAID')
                                            @if($installment->days_overdue > 0)
                                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                                    Terlambat {{ $installment->days_overdue }} hari
                                                </span>
                                            @else
                                                <span class="text-sm text-slate-500 dark:text-slate-400">Tepat waktu</span>
                                            @endif
                                        @elseif($installment->is_overdue)
                                            <div class="text-sm font-semibold text-red-600 dark:text-red-400">OVERDUE</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $installment->days_overdue }} hari</div>
                                            <div class="mt-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Denda: Rp {{ number_format((float) $installment->penalty_amount, 0, ',', '.') }}
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-500 dark:text-slate-400">Belum jatuh tempo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-state">Jadwal angsuran belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse($loan->installments as $installment)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">Angsuran #{{ $installment->installment_no }}</div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Jatuh tempo {{ $installment->due_date?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <x-status-badge :status="$installment->status" />
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Pokok</span>
                                <div class="font-semibold">Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Bunga</span>
                                <div class="font-semibold">Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Total</span>
                                <div class="font-bold">Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Sisa Pokok</span>
                                <div class="font-bold">Rp {{ number_format((float) $installment->ending_principal, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        @if($installment->is_overdue || ($installment->status === 'PAID' && $installment->days_overdue > 0))
                            <div class="mt-3 border-t border-slate-200 pt-3 text-sm dark:border-slate-700">
                                <span class="font-semibold text-red-600 dark:text-red-400">
                                    Terlambat {{ $installment->days_overdue }} hari
                                </span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">Jadwal angsuran belum tersedia.</div>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Riwayat Pembayaran" description="Pembayaran angsuran yang sudah diterima.">
            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Angsuran</th>
                                <th class="text-right">Pokok</th>
                                <th class="text-right">Bunga</th>
                                <th class="text-right">Denda</th>
                                <th class="text-right">Total</th>
                                <th>Kas / Bank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loan->payments as $payment)
                                <tr>
                                    <td><span class="table-primary text-indigo-600 dark:text-indigo-400">{{ $payment->payment_no }}</span></td>
                                    <td>{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td>#{{ $payment->installment->installment_no ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $payment->principal_amount, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $payment->interest_amount, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $payment->penalty_amount, 0, ',', '.') }}</td>
                                    <td class="text-right"><strong>Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</strong></td>
                                    <td>{{ $payment->cashAccount->code ?? '-' }} - {{ $payment->cashAccount->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-state">Belum ada riwayat pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse($loan->payments as $payment)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $payment->payment_no }}</div>
                        <div class="mt-1 font-semibold text-slate-900 dark:text-white">Angsuran #{{ $payment->installment->installment_no ?? '-' }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</div>
                        <div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</div>
                        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Pokok Rp {{ number_format((float) $payment->principal_amount, 0, ',', '.') }}
                            &middot; Bunga Rp {{ number_format((float) $payment->interest_amount, 0, ',', '.') }}
                            @if((float) $payment->penalty_amount > 0)
                                &middot; Denda Rp {{ number_format((float) $payment->penalty_amount, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada riwayat pembayaran.</div>
                @endforelse
            </div>
        </x-card>
    </div>

    <style>
        @media print {
            nav,
            aside,
            .print\:hidden {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .shadow,
            .shadow-sm,
            .shadow-md,
            .shadow-lg {
                box-shadow: none !important;
            }
        }
    </style>
</x-app-layout>
