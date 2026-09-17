<x-app-layout>
    <x-slot name="title">Preview Tutup Buku {{ $preview['year'] }}</x-slot>

    <x-page-header
        title="Preview Tutup Buku {{ $preview['year'] }}"
        description="{{ $branch->code }} - {{ $branch->name }} · {{ $preview['start_date']->format('d/m/Y') }} s/d {{ $preview['end_date']->format('d/m/Y') }}">
        <x-slot name="actions">
            <a href="{{ route('year-closing.index') }}" class="btn btn-secondary">Kembali</a>
        </x-slot>
    </x-page-header>

    @if($preview['existing']?->status === 'CLOSED')
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
            Tahun Buku {{ $preview['year'] }} sudah CLOSED. Untuk koreksi, buka detail periode dan gunakan proses Reopen.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Pendapatan</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float) $preview['income']['total_revenue'], 0, ',', '.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Beban</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float) $preview['income']['total_expense'], 0, ',', '.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">SHU / Hasil Usaha</div><div class="mt-2 text-xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $preview['income']['net_income'], 0, ',', '.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Status Validasi</div><div class="mt-2"><span class="status-badge {{ $preview['can_close'] ? 'status-active' : 'status-inactive' }}">{{ $preview['can_close'] ? 'SIAP DITUTUP' : 'BELUM SIAP' }}</span></div></x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card title="Neraca" description="Snapshot posisi keuangan per 31 Desember.">
            <div class="info-list">
                <div class="info-row"><span>Total Aset</span><strong>Rp {{ number_format((float) $preview['balance']['total_assets'], 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Liabilitas</span><strong>Rp {{ number_format((float) $preview['balance']['total_liabilities'], 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Total Ekuitas</span><strong>Rp {{ number_format((float) $preview['balance']['total_equity'], 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Selisih</span><strong>Rp {{ number_format((float) $preview['balance']['difference'], 2, ',', '.') }}</strong></div>
                <div class="info-row"><span>Status</span><strong>{{ $preview['balance']['is_balanced'] ? 'BALANCE' : 'TIDAK BALANCE' }}</strong></div>
            </div>
        </x-card>

        <x-card title="Neraca Saldo" description="Validasi total debit dan kredit pada akhir periode.">
            <div class="info-list">
                <div class="info-row"><span>Closing Debit</span><strong>Rp {{ number_format((float) $preview['trial']['totals']['closing_debit'], 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Closing Credit</span><strong>Rp {{ number_format((float) $preview['trial']['totals']['closing_credit'], 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Selisih</span><strong>Rp {{ number_format((float) $preview['trial']['difference'], 2, ',', '.') }}</strong></div>
                <div class="info-row"><span>Status</span><strong>{{ $preview['trial']['is_balanced'] ? 'BALANCE' : 'TIDAK BALANCE' }}</strong></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Pemeriksaan Sebelum Tutup Buku" description="Semua blocker harus diselesaikan sebelum periode dapat dikunci.">
            @if(empty($preview['blockers']))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                    Semua pemeriksaan lulus. Tahun buku siap ditutup.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($preview['blockers'] as $blocker)
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                            {{ $blocker }}
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    @can('closing.process')
        @if($preview['can_close'] && $preview['existing']?->status !== 'CLOSED')
            <div class="mt-6">
                <x-card title="Konfirmasi Tutup Buku" description="Setelah CLOSED, seluruh jurnal/transaksi akuntansi bertanggal dalam tahun ini akan ditolak sampai periode di-Reopen.">
                    <form method="POST" action="{{ route('year-closing.close') }}" id="close-year-form">
                        @csrf
                        <input type="hidden" name="year" value="{{ $preview['year'] }}">
                        @if(auth()->user()?->hasRole('SuperAdmin'))
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                        @endif
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="form-label" for="closing_equity_account_id">Akun Ekuitas Tujuan Hasil Usaha <span class="text-red-500">*</span></label>
                                <select id="closing_equity_account_id" name="closing_equity_account_id" class="form-select" required>
                                    <option value="">Pilih akun EQUITY</option>
                                    @foreach($equityAccounts as $account)
                                        <option value="{{ $account->id }}" @selected((string) old('closing_equity_account_id', $preview['existing']?->closing_equity_account_id) === (string) $account->id)>
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Akun ini menerima laba/SHU tahun berjalan atau menampung rugi tahun berjalan pada jurnal penutup.</p>
                                @error('closing_equity_account_id')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="close_note">Catatan Tutup Buku</label>
                                <textarea id="close_note" name="close_note" rows="3" class="form-control" placeholder="Opsional: nomor berita acara, catatan review, dll.">{{ old('close_note') }}</textarea>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                            <button class="btn btn-primary" type="submit">Tutup Buku {{ $preview['year'] }}</button>
                        </div>
                    </form>
                </x-card>
            </div>
        @endif
    @endcan

    @push('scripts')
    <script>
    (() => {
        const form = document.getElementById('close-year-form');
        if (!form) return;
        form.addEventListener('submit', event => {
            if (!window.swalConfirm) return;
            event.preventDefault();
            window.swalConfirm({
                icon: 'warning',
                title: 'Tutup Buku {{ $preview['year'] }}?',
                text: 'Periode akan dikunci. Transaksi/jurnal dengan tanggal di tahun ini tidak dapat diposting sampai dilakukan Reopen.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tutup Buku',
                cancelButtonText: 'Batal'
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    })();
    </script>
    @endpush
</x-app-layout>
