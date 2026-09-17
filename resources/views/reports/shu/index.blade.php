<x-app-layout>
    <x-slot name="title">SHU Saya</x-slot>
    <x-page-header title="SHU Saya" description="Rincian Sisa Hasil Usaha yang telah difinalisasi untuk keanggotaan Anda."></x-page-header>

    <x-card title="Pilih Tahun Buku">
        <form method="GET" action="{{ route('reports.shu.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end"><div class="w-full sm:w-56"><label class="form-label">Tahun</label><select name="year" class="form-select">@forelse($years as $year)<option value="{{ $year }}" @selected((int)$selectedYear===(int)$year)>{{ $year }}</option>@empty<option value="">Belum ada SHU</option>@endforelse</select></div><button class="btn btn-primary">Tampilkan</button></form>
    </x-card>

    @if($result)
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3"><x-card><div class="text-xs uppercase text-slate-500">Jasa Modal</div><div class="mt-2 text-2xl font-extrabold">Rp {{ number_format((float)$result->capital_shu,0,',','.') }}</div></x-card><x-card><div class="text-xs uppercase text-slate-500">Jasa Usaha</div><div class="mt-2 text-2xl font-extrabold">Rp {{ number_format((float)$result->business_shu,0,',','.') }}</div></x-card><x-card><div class="text-xs uppercase text-slate-500">Total SHU</div><div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float)$result->total_shu,0,',','.') }}</div></x-card></div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card title="Rincian Perhitungan"><div class="info-list"><div class="info-row"><span>Anggota</span><strong>{{ $member->member_number }} - {{ $member->name }}</strong></div><div class="info-row"><span>Tahun Buku</span><strong>{{ $result->period->year }}</strong></div><div class="info-row"><span>Status</span><strong>{{ $result->period->status }}</strong></div><div class="info-row"><span>Rata-rata Saldo Modal</span><strong>Rp {{ number_format((float)$result->capital_basis,0,',','.') }}</strong></div><div class="info-row"><span>Proporsi Jasa Modal</span><strong>{{ number_format((float)$result->capital_ratio,6,',','.') }}%</strong></div><div class="info-row"><span>Bunga Pinjaman Dibayar</span><strong>Rp {{ number_format((float)$result->business_basis,0,',','.') }}</strong></div><div class="info-row"><span>Proporsi Jasa Usaha</span><strong>{{ number_format((float)$result->business_ratio,6,',','.') }}%</strong></div></div></x-card>
            <x-card title="Basis Jasa Modal" description="Jenis simpanan yang ditetapkan sebagai basis pada tahun buku ini."><div class="space-y-2">@foreach($result->period->savingTypes as $type)<div class="rounded-lg border border-slate-200 px-4 py-3 dark:border-slate-700"><strong>{{ $type->code }} - {{ $type->name }}</strong></div>@endforeach</div><div class="mt-4 text-sm text-slate-500">Jasa Usaha dihitung dari bunga pinjaman yang benar-benar dibayar pada periode tahun buku.</div></x-card>
        </div>
    @else
        <div class="mt-6"><x-card><div class="empty-state">Belum ada SHU yang telah difinalisasi untuk Anda.</div></x-card></div>
    @endif
</x-app-layout>
