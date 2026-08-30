<x-app-layout>
    <x-slot name="title">Audit Log</x-slot>
    <x-page-header title="Audit Log" description="Riwayat aktivitas dan perubahan data pada aplikasi koperasi." />
    <x-card>
        <form method="GET" action="{{ route('audit-logs.index') }}" class="mb-5 grid grid-cols-1 gap-3 xl:grid-cols-[1fr_160px_220px_160px_160px_auto]">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari user, deskripsi, model, IP..." class="form-control">
            <select name="action" class="form-select"><option value="">Semua Action</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>@endforeach</select>
            <select name="user_id" class="form-select"><option value="">Semua User</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string)request('user_id') === (string)$user->id)>{{ $user->name }}</option>@endforeach</select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="Tanggal mulai">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="Tanggal akhir">
            <div class="flex gap-2"><button type="submit" class="btn btn-primary flex-1">Filter</button><a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Reset</a></div>
        </form>

        <div class="hidden md:block"><div class="table-wrapper"><table class="data-table"><thead><tr><th>Waktu</th><th>User</th><th>Cabang</th><th>Action</th><th>Deskripsi</th><th>Object</th><th class="text-right">Detail</th></tr></thead><tbody>
        @forelse($logs as $log)
            <tr>
                <td><span class="table-primary">{{ $log->created_at?->format('d/m/Y') }}</span><span class="table-secondary">{{ $log->created_at?->format('H:i:s') }}</span></td>
                <td><span class="table-primary">{{ $log->user?->name ?? 'System' }}</span><span class="table-secondary">{{ $log->user?->email ?? '-' }}</span></td>
                <td>{{ $log->branch?->name ?? '-' }}</td>
                <td><x-status-badge :status="$log->action" /></td>
                <td><div class="max-w-sm truncate">{{ $log->description ?: '-' }}</div></td>
                <td><span class="table-primary">{{ class_basename($log->auditable_type ?: '-') }}</span><span class="table-secondary">ID: {{ $log->auditable_id ?? '-' }}</span></td>
                <td><div class="flex justify-end"><a href="{{ route('audit-logs.show', $log) }}" class="btn btn-secondary">Detail</a></div></td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty-state">Belum ada audit log.</td></tr>
        @endforelse
        </tbody></table></div></div>

        <div class="space-y-3 md:hidden">
        @forelse($logs as $log)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                <div class="flex items-start justify-between gap-3"><div class="min-w-0"><div class="truncate font-semibold text-slate-900 dark:text-white">{{ $log->description ?: $log->action }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $log->created_at?->format('d/m/Y H:i:s') }}</div></div><x-status-badge :status="$log->action" /></div>
                <div class="mt-3 text-sm text-slate-600 dark:text-slate-300"><div><strong>User:</strong> {{ $log->user?->name ?? 'System' }}</div><div><strong>Cabang:</strong> {{ $log->branch?->name ?? '-' }}</div><div><strong>Object:</strong> {{ class_basename($log->auditable_type ?: '-') }} #{{ $log->auditable_id ?? '-' }}</div></div>
                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-secondary mt-4 w-full">Lihat Detail</a>
            </div>
        @empty
            <x-empty-state title="Belum ada audit log" description="Aktivitas aplikasi akan tampil di sini." />
        @endforelse
        </div>

        @if($logs->hasPages())<div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">{{ $logs->links() }}</div>@endif
    </x-card>
</x-app-layout>
