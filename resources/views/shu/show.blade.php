<x-app-layout>
    <x-slot name="title">SHU {{ $period->year }}</x-slot>

    <x-page-header title="SHU Tahun Buku {{ $period->year }}" description="{{ $period->branch?->code }} - {{ $period->branch?->name }} · {{ $period->start_date->format('d/m/Y') }} s/d {{ $period->end_date->format('d/m/Y') }}">
        <x-slot name="actions"><div class="flex flex-wrap gap-2">@can('shu.create') @if(!$period->isLocked())<a href="{{ route('shu.edit',$period) }}" class="btn btn-secondary">Edit Parameter</a>@endif @endcan @can('shu.calculate') @if(!$period->isLocked())<form method="POST" action="{{ route('shu.calculate',$period) }}" class="shu-confirm-form" data-title="Hitung SHU?" data-text="Hasil kalkulasi sebelumnya akan diganti.">@csrf<button class="btn btn-primary">Hitung SHU</button></form>@endif @endcan</div></x-slot>
    </x-page-header>

    @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ $errors->first() }}</div>@endif

    <div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-800/60">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <strong>Sumber SHU: Snapshot Tutup Buku</strong>
                <div class="mt-1 text-slate-500 dark:text-slate-400">
                    @if($period->yearClosing)
                        Tahun Buku {{ $period->yearClosing->year }} · {{ $period->yearClosing->status }} · closed {{ $period->yearClosing->closed_at?->format('d/m/Y H:i') ?? '-' }}
                    @else
                        Belum terhubung. Tahun buku harus CLOSED sebelum SHU dapat dihitung.
                    @endif
                </div>
            </div>
            @if($period->yearClosing && Route::has('year-closing.show'))
                <a href="{{ route('year-closing.show', $period->yearClosing) }}" class="btn btn-secondary">Lihat Tutup Buku</a>
            @endif
        </div>
    </div>

    @php($memberAllocation = $period->allocations->firstWhere('is_member_pool', true))
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Status</div><div class="mt-2"><span class="status-badge {{ in_array($period->status,['FINALIZED','PAID']) ? 'status-active' : '' }}">{{ $period->status }}</span></div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Total Pendapatan</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float)$period->total_revenue,0,',','.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">Total Beban</div><div class="mt-2 text-xl font-extrabold">Rp {{ number_format((float)$period->total_expense,0,',','.') }}</div></x-card>
        <x-card><div class="text-xs font-semibold uppercase text-slate-500">SHU Tahun Berjalan</div><div class="mt-2 text-xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float)$period->net_shu,0,',','.') }}</div></x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card title="Alokasi SHU" description="Komposisi sesuai parameter RAT tahun buku ini.">
            <div class="space-y-3">@foreach($period->allocations as $allocation)<div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3 last:border-0 dark:border-slate-800"><div><strong>{{ $allocation->name }}</strong>@if($allocation->is_member_pool)<span class="ml-2 status-badge status-active">Bagian Anggota</span>@endif<div class="text-xs text-slate-500">{{ number_format((float)$allocation->percentage,2,',','.') }}% @if($allocation->account) · {{ $allocation->account->code }} - {{ $allocation->account->name }} @endif</div></div><strong>Rp {{ number_format((float)$allocation->amount,0,',','.') }}</strong></div>@endforeach</div>
        </x-card>
        <x-card title="Parameter Anggota" description="Jasa Modal dihitung dari rata-rata saldo harian jenis simpanan terpilih; Jasa Usaha dari bunga pinjaman yang benar-benar dibayar.">
            <div class="info-list"><div class="info-row"><span>Pool Anggota</span><strong>Rp {{ number_format((float)($memberAllocation?->amount ?? 0),0,',','.') }}</strong></div><div class="info-row"><span>Jasa Modal</span><strong>{{ number_format((float)$period->capital_share_percentage,2,',','.') }}%</strong></div><div class="info-row"><span>Jasa Usaha</span><strong>{{ number_format((float)$period->business_share_percentage,2,',','.') }}%</strong></div><div class="info-row"><span>Jenis Simpanan</span><strong>{{ $period->savingTypes->map(fn($t)=>$t->code.' - '.$t->name)->join(', ') ?: '-' }}</strong></div><div class="info-row"><span>Posting Jurnal</span><strong>{{ $period->post_journal ? 'Ya' : 'Tidak' }}</strong></div>@if($period->journalEntry)<div class="info-row"><span>No. Jurnal</span><strong>{{ $period->journalEntry->journal_no }}</strong></div>@endif</div>
        </x-card>
    </div>

    @if($period->status === 'CALCULATED')
        @can('shu.finalize')<div class="mt-6"><x-card title="Finalisasi" description="Setelah finalisasi, parameter dan hasil SHU dikunci. Anggota mulai dapat melihat SHU mereka."><form method="POST" action="{{ route('shu.finalize',$period) }}" class="shu-confirm-form" data-title="Finalisasi SHU?" data-text="Data akan dikunci dan tidak dapat dihitung ulang.">@csrf<button class="btn btn-primary">Finalisasi SHU</button></form></x-card></div>@endcan
    @endif

    @if($period->status === 'FINALIZED')
        @can('shu.pay')<div class="mt-6"><x-card title="Tandai Sudah Dibayar" description="Penandaan ini mencatat status distribusi. Tidak membuat transaksi kas individual anggota."><form method="POST" action="{{ route('shu.paid',$period) }}" class="shu-confirm-form" data-title="Tandai PAID?" data-text="Pastikan pembayaran/distribusi SHU sudah dilakukan.">@csrf<div class="grid grid-cols-1 gap-4 md:grid-cols-[200px_1fr_auto] md:items-end"><div><label class="form-label">Tanggal Bayar</label><input type="date" name="paid_date" value="{{ now()->toDateString() }}" class="form-control" required></div><div><label class="form-label">Catatan</label><input type="text" name="payment_note" class="form-control" placeholder="Opsional"></div><button class="btn btn-primary">Tandai PAID</button></div></form></x-card></div>@endcan
    @endif

    <div class="mt-6"><x-card title="Hasil SHU Anggota" description="Snapshot hasil kalkulasi per anggota.">
        <div class="hidden md:block table-wrapper"><table class="data-table"><thead><tr><th>Anggota</th><th class="text-right">Rata-rata Modal</th><th class="text-right">Bunga Dibayar</th><th class="text-right">Jasa Modal</th><th class="text-right">Jasa Usaha</th><th class="text-right">Total SHU</th></tr></thead><tbody>@forelse($results as $row)<tr><td><span class="table-primary">{{ $row->member_number_snapshot }}</span><span class="table-secondary">{{ $row->member_name_snapshot }}</span></td><td class="text-right">Rp {{ number_format((float)$row->capital_basis,0,',','.') }}</td><td class="text-right">Rp {{ number_format((float)$row->business_basis,0,',','.') }}</td><td class="text-right">Rp {{ number_format((float)$row->capital_shu,0,',','.') }}</td><td class="text-right">Rp {{ number_format((float)$row->business_shu,0,',','.') }}</td><td class="text-right font-bold">Rp {{ number_format((float)$row->total_shu,0,',','.') }}</td></tr>@empty<tr><td colspan="6" class="empty-state">Belum ada hasil. Klik Hitung SHU.</td></tr>@endforelse</tbody></table></div>
        <div class="space-y-3 md:hidden">@forelse($results as $row)<div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"><div class="font-bold">{{ $row->member_name_snapshot }}</div><div class="text-xs text-slate-500">{{ $row->member_number_snapshot }}</div><div class="mt-4 grid grid-cols-2 gap-3 text-sm"><div><span class="text-slate-500">Jasa Modal</span><div class="font-semibold">Rp {{ number_format((float)$row->capital_shu,0,',','.') }}</div></div><div><span class="text-slate-500">Jasa Usaha</span><div class="font-semibold">Rp {{ number_format((float)$row->business_shu,0,',','.') }}</div></div></div><div class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-700"><div class="flex justify-between"><span>Total SHU</span><strong>Rp {{ number_format((float)$row->total_shu,0,',','.') }}</strong></div></div></div>@empty<div class="empty-state">Belum ada hasil. Klik Hitung SHU.</div>@endforelse</div>
        <div class="mt-4">{{ $results->links() }}</div>
    </x-card></div>

@push('scripts')
<script>
(() => {
 document.querySelectorAll('.shu-confirm-form').forEach(form => form.addEventListener('submit', e => {
   if (!window.swalConfirm) return;
   e.preventDefault();
   window.swalConfirm({icon:'question',title:form.dataset.title || 'Konfirmasi',text:form.dataset.text || '',showCancelButton:true,confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Batal'}).then(r => { if (r.isConfirmed) form.submit(); });
 }));
})();
</script>
@endpush
</x-app-layout>
