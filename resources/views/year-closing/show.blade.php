<x-app-layout>
    <x-slot name="title">Tahun Buku {{ $yearClosing->year }}</x-slot>

    <x-page-header
        title="Tahun Buku {{ $yearClosing->year }}"
        description="{{ $yearClosing->branch?->code }} - {{ $yearClosing->branch?->name }} · {{ $yearClosing->start_date->format('d/m/Y') }} s/d {{ $yearClosing->end_date->format('d/m/Y') }}">
        <x-slot name="actions">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('year-closing.index') }}" class="btn btn-secondary">Kembali</a>
                @if($yearClosing->isClosed() && Route::has('shu.index'))
                    @can('shu.view')<a href="{{ route('shu.index', ['year' => $yearClosing->year, 'branch_id' => $yearClosing->branch_id]) }}" class="btn btn-primary">Buka SHU</a>@endcan
                @endif
            </div>
        </x-slot>
    </x-page-header>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Status</div><div class="mt-2"><span class="status-badge {{ $yearClosing->isClosed() ? 'status-active' : 'status-inactive' }}">{{ $yearClosing->status }}</span></div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Pendapatan</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float) $yearClosing->total_revenue, 0, ',', '.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Beban</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float) $yearClosing->total_expense, 0, ',', '.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">SHU Snapshot</div><div class="mt-2 text-xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $yearClosing->net_shu, 0, ',', '.') }}</div></x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card title="Snapshot Akhir Tahun" description="Nilai ini menjadi referensi resmi untuk modul SHU.">
            <div class="info-list">
                <div class="info-row"><span>Total Aset</span><strong>Rp {{ number_format((float) $yearClosing->total_assets, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Liabilitas</span><strong>Rp {{ number_format((float) $yearClosing->total_liabilities, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Ekuitas</span><strong>Rp {{ number_format((float) $yearClosing->total_equity, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Selisih Neraca</span><strong>Rp {{ number_format((float) $yearClosing->balance_difference, 2, ',', '.') }}</strong></div>
                <div class="info-row"><span>TB Debit</span><strong>Rp {{ number_format((float) $yearClosing->trial_balance_debit, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>TB Credit</span><strong>Rp {{ number_format((float) $yearClosing->trial_balance_credit, 0, ',', '.') }}</strong></div>
            </div>
        </x-card>

        <x-card title="Audit Tutup Buku" description="Informasi close/reopen terakhir.">
            <div class="info-list">
                <div class="info-row"><span>Jumlah Close</span><strong>{{ $yearClosing->close_count }}x</strong></div>
                <div class="info-row"><span>Ditutup Oleh</span><strong>{{ $yearClosing->closedBy?->name ?? '-' }}</strong></div>
                <div class="info-row"><span>Closed At</span><strong>{{ $yearClosing->closed_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
                <div class="info-row"><span>Akun Ekuitas</span><strong>{{ $yearClosing->closingEquityAccount ? $yearClosing->closingEquityAccount->code . ' - ' . $yearClosing->closingEquityAccount->name : '-' }}</strong></div>
                <div class="info-row"><span>Jurnal Penutup</span><strong>{{ $yearClosing->journalEntry?->journal_no ?? '-' }}</strong></div>
                @if($yearClosing->reopenJournalEntry)<div class="info-row"><span>Jurnal Reversal</span><strong>{{ $yearClosing->reopenJournalEntry->journal_no }}</strong></div>@endif
                <div class="info-row"><span>Catatan</span><strong>{{ $yearClosing->close_note ?: '-' }}</strong></div>
                @if($yearClosing->reopened_at)
                    <div class="info-row"><span>Reopened Oleh</span><strong>{{ $yearClosing->reopenedBy?->name ?? '-' }}</strong></div>
                    <div class="info-row"><span>Reopened At</span><strong>{{ $yearClosing->reopened_at?->format('d/m/Y H:i') }}</strong></div>
                    <div class="info-row"><span>Alasan Reopen</span><strong>{{ $yearClosing->reopen_reason }}</strong></div>
                @endif
            </div>
        </x-card>
    </div>

    @can('closing.process')
        @if($yearClosing->isClosed())
            <div class="mt-6">
                <x-card title="Reopen Tahun Buku" description="Gunakan hanya untuk koreksi yang memang diperlukan. SHU FINALIZED/PAID akan memblokir proses Reopen.">
                    <form method="POST" action="{{ route('year-closing.reopen', $yearClosing) }}" id="reopen-form">
                        @csrf
                        <div>
                            <label class="form-label" for="reopen_reason">Alasan Reopen <span class="text-red-500">*</span></label>
                            <textarea id="reopen_reason" name="reopen_reason" rows="3" minlength="10" maxlength="2000" class="form-control" required placeholder="Contoh: koreksi jurnal penyusutan berdasarkan hasil review auditor.">{{ old('reopen_reason') }}</textarea>
                        </div>
                        <div class="mt-5 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                            <button class="btn btn-secondary" type="submit">Reopen Tahun Buku</button>
                        </div>
                    </form>
                </x-card>
            </div>
        @endif
    @endcan

    @push('scripts')
    <script>
    (() => {
        const form = document.getElementById('reopen-form');
        if (!form) return;
        form.addEventListener('submit', event => {
            if (!window.swalConfirm) return;
            event.preventDefault();
            window.swalConfirm({
                icon: 'warning',
                title: 'Reopen Tahun Buku {{ $yearClosing->year }}?',
                text: 'Periode akan kembali terbuka. Jika SHU baru CALCULATED, hasil kalkulasinya akan direset menjadi DRAFT.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reopen',
                cancelButtonText: 'Batal'
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    })();
    </script>
    @endpush
</x-app-layout>
