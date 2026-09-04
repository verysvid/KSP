<x-app-layout>
    <x-slot name="title">Simulasi Angsuran</x-slot>

    <x-page-header
        title="Simulasi Angsuran"
        description="{{ $loan->loan_no }} - {{ $member->name }}">
        <x-slot name="actions">
            <a href="{{ route('member-loan-applications.show', $loan) }}"
               class="btn btn-secondary">
                Kembali ke Detail
            </a>

            @can('member-loan-application.edit')
                <a href="{{ route('member-loan-applications.edit', $loan) }}"
                   class="btn btn-secondary">
                    Edit Pengajuan
                </a>
            @endcan

            @can('member-loan-application.submit')
                <form
                    method="POST"
                    action="{{ route('member-loan-applications.submit', $loan) }}"
                    class="member-loan-submit-form"
                    data-loan-no="{{ $loan->loan_no }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-primary">
                        Submit Pengajuan
                    </button>
                </form>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4
                text-sm text-amber-800
                dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
        <div class="font-bold">Simulasi sementara</div>
        <p class="mt-1">
            Jadwal ini hanya memberikan gambaran perkiraan cicilan dan tidak disimpan
            sebagai tagihan. Jadwal resmi akan dibentuk setelah pengajuan disetujui dan
            pinjaman dicairkan oleh petugas.
        </p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Pokok</span>
                <div class="stat-icon">Rp</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format((float) $totalPrincipal, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Bunga</span>
                <div class="stat-icon">%</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format((float) $totalInterest, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Angsuran</span>
                <div class="stat-icon">∑</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format((float) $totalInstallment, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card
            title="Dasar Simulasi"
            description="Data Draft yang digunakan dalam perhitungan.">
            <div class="info-list">
                <div class="info-row">
                    <span>Jenis Pinjaman</span>
                    <strong class="text-right">
                        {{ $loan->loanType->code ?? '-' }} -
                        {{ $loan->loanType->name ?? '-' }}
                    </strong>
                </div>
                <div class="info-row">
                    <span>Nominal</span>
                    <strong>
                        Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                    </strong>
                </div>
                <div class="info-row">
                    <span>Metode</span>
                    <strong>{{ $loan->interest_type }}</strong>
                </div>
                <div class="info-row">
                    <span>Bunga</span>
                    <strong>
                        {{ str_replace('.', ',', rtrim(rtrim(number_format((float) $loan->interest_rate, 4, '.', ''), '0'), '.')) }}%
                    </strong>
                </div>
                <div class="info-row">
                    <span>Tenor</span>
                    <strong>{{ $loan->tenor_months }} bulan</strong>
                </div>
                <div class="info-row">
                    <span>Jatuh Tempo</span>
                    <strong>Tanggal {{ $loan->due_day }}</strong>
                </div>
                <div class="info-row">
                    <span>Tanggal Dasar</span>
                    <strong>{{ $loan->application_date?->format('d/m/Y') ?? '-' }}</strong>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                        text-xs text-slate-600
                        dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                Simulasi menggunakan tanggal pengajuan sebagai asumsi tanggal pencairan.
                Tanggal jadwal resmi dapat berbeda sesuai tanggal pencairan sebenarnya.
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Jadwal Simulasi Angsuran"
            description="Perkiraan rincian cicilan setiap bulan.">

            {{-- Desktop --}}
            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Angsuran</th>
                            <th>Jatuh Tempo</th>
                            <th>Saldo Awal</th>
                            <th>Pokok</th>
                            <th>Bunga</th>
                            <th>Total</th>
                            <th>Sisa Pokok</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schedule as $row)
                            <tr>
                                <td>#{{ $row['installment_no'] }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') }}
                                </td>
                                <td>
                                    Rp {{ number_format((float) $row['opening_principal'], 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format((float) $row['principal_amount'], 0, ',', '.') }}
                                </td>
                                <td>
                                    Rp {{ number_format((float) $row['interest_amount'], 0, ',', '.') }}
                                </td>
                                <td>
                                    <strong>
                                        Rp {{ number_format((float) $row['installment_amount'], 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td>
                                    Rp {{ number_format((float) $row['ending_principal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    Jadwal simulasi belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if(!empty($schedule))
                            <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>
                                    Rp {{ number_format((float) $totalPrincipal, 0, ',', '.') }}
                                </th>
                                <th>
                                    Rp {{ number_format((float) $totalInterest, 0, ',', '.') }}
                                </th>
                                <th>
                                    Rp {{ number_format((float) $totalInstallment, 0, ',', '.') }}
                                </th>
                                <th>-</th>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Mobile --}}
            <div class="space-y-3 md:hidden">
                @forelse($schedule as $row)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                                dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">
                                    Angsuran #{{ $row['installment_no'] }}
                                </div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Jatuh tempo
                                    {{ \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    Total
                                </div>
                                <div class="font-bold text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format((float) $row['installment_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Saldo Awal</span>
                                <div class="font-semibold">
                                    Rp {{ number_format((float) $row['opening_principal'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Pokok</span>
                                <div class="font-semibold">
                                    Rp {{ number_format((float) $row['principal_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Bunga</span>
                                <div class="font-semibold">
                                    Rp {{ number_format((float) $row['interest_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">Sisa Pokok</span>
                                <div class="font-semibold">
                                    Rp {{ number_format((float) $row['ending_principal'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        title="Jadwal simulasi belum tersedia"
                        description="Periksa kembali data pengajuan pinjaman Anda." />
                @endforelse
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.member-loan-submit-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Submit Pengajuan Pinjaman?',
                            html:
                                'Pengajuan <strong>' +
                                escapeMemberLoanSimulationHtml(form.dataset.loanNo) +
                                '</strong> akan dikirim untuk proses approval.',
                            confirmButtonText: 'Ya, Submit',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            function escapeMemberLoanSimulationHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value || '');
                return div.innerHTML;
            }
        </script>
    @endpush
</x-app-layout>
