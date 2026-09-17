<x-app-layout>
    <x-slot name="title">Sisa Hasil Usaha</x-slot>

    <x-page-header title="Sisa Hasil Usaha (SHU)" description="Kelola perhitungan, finalisasi, dan distribusi SHU per tahun buku.">
        @can('shu.create')
            <x-slot name="actions"><a href="{{ route('shu.create') }}" class="btn btn-primary">Buat Periode SHU</a></x-slot>
        @endcan
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('shu.index') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4 md:items-end">
            @if($isSuperAdmin)
                <div><label class="form-label">Cabang</label><select name="branch_id" class="form-select"><option value="">Semua Cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)request('branch_id')===(string)$branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>@endforeach</select></div>
            @endif
            <div><label class="form-label">Tahun</label><input type="number" name="year" value="{{ request('year') }}" class="form-control" min="2000" max="2100"></div>
            <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua Status</option>@foreach(['DRAFT','CALCULATED','FINALIZED','PAID'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div>
            <div class="flex gap-2"><button class="btn btn-primary">Filter</button><a href="{{ route('shu.index') }}" class="btn btn-secondary">Reset</a></div>
        </form>

        <div class="hidden md:block table-wrapper">
            <table class="data-table"><thead><tr><th>Tahun</th><th>Cabang</th><th>Periode</th><th>Status</th><th class="text-right">SHU</th><th class="text-right">Bagian Anggota</th><th></th></tr></thead><tbody>
            @forelse($periods as $period)
                @php($memberAllocation = $period->allocations->firstWhere('is_member_pool', true))
                <tr><td class="font-semibold">{{ $period->year }}</td><td>{{ $period->branch?->code }} - {{ $period->branch?->name }}</td><td>{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}</td><td><span class="status-badge {{ in_array($period->status,['FINALIZED','PAID']) ? 'status-active' : '' }}">{{ $period->status }}</span></td><td class="text-right font-semibold">Rp {{ number_format((float)$period->net_shu,0,',','.') }}</td><td class="text-right">Rp {{ number_format((float)($memberAllocation?->amount ?? 0),0,',','.') }}</td><td class="text-right"><a href="{{ route('shu.show',$period) }}" class="btn btn-secondary">Detail</a></td></tr>
            @empty<tr><td colspan="7" class="empty-state">Belum ada periode SHU.</td></tr>@endforelse
            </tbody></table>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($periods as $period)
                @php($memberAllocation = $period->allocations->firstWhere('is_member_pool', true))
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"><div class="flex justify-between gap-3"><div><div class="text-xs text-slate-500">Tahun Buku</div><div class="text-xl font-bold">{{ $period->year }}</div><div class="mt-1 text-sm text-slate-500">{{ $period->branch?->code }} - {{ $period->branch?->name }}</div></div><span class="status-badge {{ in_array($period->status,['FINALIZED','PAID']) ? 'status-active' : '' }}">{{ $period->status }}</span></div><div class="mt-4 grid grid-cols-2 gap-3 text-sm"><div><div class="text-slate-500">Total SHU</div><strong>Rp {{ number_format((float)$period->net_shu,0,',','.') }}</strong></div><div><div class="text-slate-500">Bagian Anggota</div><strong>Rp {{ number_format((float)($memberAllocation?->amount ?? 0),0,',','.') }}</strong></div></div><a href="{{ route('shu.show',$period) }}" class="btn btn-secondary mt-4 w-full">Detail</a></div>
            @empty<div class="empty-state">Belum ada periode SHU.</div>@endforelse
        </div>
        <div class="mt-4">{{ $periods->links() }}</div>
    </x-card>
</x-app-layout>
