<x-app-layout>
    <x-slot name="title">Detail Pengajuan Pinjaman Saya</x-slot>

    <x-page-header
        title="Detail Pengajuan Pinjaman Saya"
        description="{{ $loan->loan_no }} - {{ $member->name }}">
        <x-slot name="actions">
            <a href="{{ route('member-loan-applications.index') }}"
               class="btn btn-secondary">Kembali</a>

            @if($loan->status === 'DRAFT')
                @can('member-loan-application.edit')
                    <a href="{{ route('member-loan-applications.edit', $loan) }}"
                       class="btn btn-secondary">Edit Pengajuan</a>
                @endcan

                @can('member-loan-application.view')
                    <a href="{{ route('member-loan-applications.simulation', $loan) }}"
                       class="btn btn-secondary">Simulasi Angsuran</a>
                @endcan

                @can('member-loan-application.submit')
                    <form method="POST"
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
            @endif
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center
                            rounded-2xl bg-indigo-100 text-2xl font-extrabold text-indigo-700
                            dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($loan->loanType->code ?? 'LN', 0, 2)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $member->name }}
                </h2>
                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ $loan->loan_no }}
                </div>
                <div class="mt-3">
                    <x-status-badge :status="$loan->status" />
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 px-4 py-4 dark:bg-slate-800/60">
                    <div class="text-xs font-medium uppercase tracking-wide
                                text-slate-500 dark:text-slate-400">
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
            title="Informasi Pengajuan"
            description="Detail pengajuan pinjaman Anda.">
            <div class="info-list">
                <div class="info-row">
                    <span>No. Pinjaman</span>
                    <strong>{{ $loan->loan_no }}</strong>
                </div>
                <div class="info-row">
                    <span>Tanggal Pengajuan</span>
                    <strong>{{ $loan->application_date?->format('d/m/Y') ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Cabang</span>
                    <strong>
                        {{ $loan->branch->code ?? '-' }} - {{ $loan->branch->name ?? '-' }}
                    </strong>
                </div>
                <div class="info-row">
                    <span>No. Anggota</span>
                    <strong>{{ $member->member_number ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Jenis Pinjaman</span>
                    <strong>
                        {{ $loan->loanType->code ?? '-' }} - {{ $loan->loanType->name ?? '-' }}
                    </strong>
                </div>
                <div class="info-row">
                    <span>Nominal Pinjaman</span>
                    <strong>Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Metode Bunga</span>
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
                    <span>Tanggal Jatuh Tempo Bulanan</span>
                    <strong>Tanggal {{ $loan->due_day }}</strong>
                </div>
                <div class="info-row">
                    <span>Status</span>
                    <strong><x-status-badge :status="$loan->status" /></strong>
                </div>
                <div class="info-row">
                    <span>Catatan</span>
                    <strong class="max-w-md">{{ $loan->notes ?: '-' }}</strong>
                </div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card
            title="Proses Pengajuan"
            description="Riwayat proses dan keputusan pengajuan.">
            <div class="info-list">
                <div class="info-row">
                    <span>Dibuat Pada</span>
                    <strong>{{ $loan->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Waktu Submit</span>
                    <strong>{{ $loan->submitted_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                @if($loan->approved_at)
                    <div class="info-row">
                        <span>Disetujui Oleh</span>
                        <strong>{{ $loan->approvedBy->name ?? '-' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Waktu Approval</span>
                        <strong>{{ $loan->approved_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif

                @if($loan->rejected_at)
                    <div class="info-row">
                        <span>Ditolak Oleh</span>
                        <strong>{{ $loan->rejectedBy->name ?? '-' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Waktu Penolakan</span>
                        <strong>{{ $loan->rejected_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Alasan Penolakan</span>
                        <strong class="max-w-md text-red-600 dark:text-red-400">
                            {{ $loan->rejection_reason ?: '-' }}
                        </strong>
                    </div>
                @endif

                @if($loan->disbursed_at)
                    <div class="info-row">
                        <span>Waktu Pencairan</span>
                        <strong>{{ $loan->disbursed_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card
            title="Nilai Pinjaman"
            description="Ringkasan nilai pinjaman Anda.">
            <div class="info-list">
                <div class="info-row">
                    <span>Total Pokok</span>
                    <strong>Rp {{ number_format((float) $loan->total_principal, 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Total Bunga</span>
                    <strong>Rp {{ number_format((float) $loan->total_interest, 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Total Angsuran</span>
                    <strong>Rp {{ number_format((float) $loan->total_installment, 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Sisa Pokok</span>
                    <strong>Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($loan->status === 'DRAFT')
                <div class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3
                            text-sm text-indigo-700
                            dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                    Nilai bunga dan angsuran resmi belum dibentuk. Gunakan tombol
                    <strong>Simulasi Angsuran</strong> untuk melihat perkiraan cicilan.
                </div>
            @elseif(in_array($loan->status, ['SUBMITTED', 'APPROVED'], true))
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                            text-sm text-slate-600
                            dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                    Jadwal resmi akan dibuat setelah proses pencairan pinjaman.
                </div>
            @endif
        </x-card>
    </div>

    @if($loan->status === 'APPROVED')
        <div class="mt-6">
            <x-card
                title="Menunggu Pencairan"
                description="Pengajuan telah disetujui oleh petugas.">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-4
                            text-sm text-emerald-700
                            dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Pengajuan Anda telah <strong>APPROVED</strong> dan sedang menunggu
                    proses pencairan oleh petugas koperasi.
                </div>
            </x-card>
        </div>
    @endif

    @if($loan->status === 'DRAFT')
        @can('member-loan-application.delete')
            <div class="mt-6 flex justify-end">
                <form method="POST"
                      action="{{ route('member-loan-applications.destroy', $loan) }}"
                      class="member-loan-delete-form"
                      data-loan-no="{{ $loan->loan_no }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus Draft</button>
                </form>
            </div>
        @endcan
    @endif

    @if(in_array($loan->status, ['ACTIVE', 'PAID_OFF'], true))
        <div class="mt-6">
            <x-card
                title="Jadwal Angsuran"
                description="Jadwal angsuran resmi yang terbentuk setelah pencairan.">
                <div class="hidden md:block">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>Angsuran</th>
                                <th>Jatuh Tempo</th>
                                <th>Pokok</th>
                                <th>Bunga</th>
                                <th>Total</th>
                                <th>Sisa Pokok</th>
                                <th>Status</th>
                                <th>Keterlambatan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($loan->installments as $installment)
                                <tr>
                                    <td>#{{ $installment->installment_no }}</td>
                                    <td>{{ $installment->due_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td>Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <strong>Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>Rp {{ number_format((float) $installment->ending_principal, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$installment->status" /></td>
                                    <td>
                                        @if($installment->status === 'PAID')
                                            @if($installment->days_overdue > 0)
                                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                                    Terlambat {{ $installment->days_overdue }} hari
                                                </span>
                                            @else
                                                <span class="text-sm text-slate-500 dark:text-slate-400">
                                                    Tepat waktu
                                                </span>
                                            @endif
                                        @elseif($installment->is_overdue)
                                            <div class="text-sm font-semibold text-red-600 dark:text-red-400">
                                                OVERDUE
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $installment->days_overdue }} hari
                                            </div>
                                            <div class="mt-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Denda: Rp {{ number_format((float) $installment->penalty_amount, 0, ',', '.') }}
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-500 dark:text-slate-400">
                                                Belum jatuh tempo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        Jadwal angsuran belum tersedia.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 md:hidden">
                    @forelse($loan->installments as $installment)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                                    dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        Angsuran #{{ $installment->installment_no }}
                                    </div>
                                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        Jatuh tempo {{ $installment->due_date?->format('d/m/Y') ?? '-' }}
                                    </div>
                                </div>
                                <x-status-badge :status="$installment->status" />
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">Pokok</span>
                                    <div class="font-semibold">
                                        Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">Bunga</span>
                                    <div class="font-semibold">
                                        Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-slate-500 dark:text-slate-400">Total Angsuran</span>
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            title="Jadwal angsuran belum tersedia"
                            description="Hubungi petugas koperasi bila pinjaman sudah dicairkan." />
                    @endforelse
                </div>
            </x-card>
        </div>
    @endif

    @if($loan->payments->isNotEmpty())
        <div class="mt-6">
            <x-card
                title="Riwayat Pembayaran"
                description="Pembayaran angsuran yang sudah diterima koperasi.">
                <div class="hidden md:block">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>No. Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Angsuran</th>
                                <th>Pokok</th>
                                <th>Bunga</th>
                                <th>Denda</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loan->payments as $payment)
                                <tr>
                                    <td>
                                        <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                            {{ $payment->payment_no }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td>#{{ $payment->installment->installment_no ?? '-' }}</td>
                                    <td>Rp {{ number_format((float) $payment->principal_amount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((float) $payment->interest_amount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((float) $payment->penalty_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <strong>Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 md:hidden">
                    @foreach($loan->payments as $payment)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                                    dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $payment->payment_no }}
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Angsuran #{{ $payment->installment->installment_no ?? '-' }}
                            </div>
                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $payment->payment_date?->format('d/m/Y') ?? '-' }}
                            </div>
                            <div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif

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
                                escapeMemberLoanHtml(form.dataset.loanNo) +
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

            document.querySelectorAll('.member-loan-delete-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'warning',
                            title: 'Hapus Draft Pengajuan?',
                            html:
                                'Draft <strong>' +
                                escapeMemberLoanHtml(form.dataset.loanNo) +
                                '</strong> akan dihapus permanen.',
                            confirmButtonText: 'Ya, Hapus',
                            confirmButtonColor: '#dc2626',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            function escapeMemberLoanHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value || '');
                return div.innerHTML;
            }
        </script>
    @endpush
</x-app-layout>
