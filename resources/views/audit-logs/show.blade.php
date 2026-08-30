<x-app-layout>
    <x-slot name="title">Detail Audit Log</x-slot>
    <x-page-header title="Detail Audit Log" description="Informasi lengkap aktivitas #{{ $auditLog->id }}."><x-slot name="actions"><a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Kembali</a></x-slot></x-page-header>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-card title="Informasi Aktivitas"><div class="info-list">
            <div class="info-row"><span>Waktu</span><strong>{{ $auditLog->created_at?->format('d/m/Y H:i:s') ?? '-' }}</strong></div>
            <div class="info-row"><span>User</span><strong>{{ $auditLog->user?->name ?? 'System' }}</strong></div>
            <div class="info-row"><span>Email</span><strong>{{ $auditLog->user?->email ?? '-' }}</strong></div>
            <div class="info-row"><span>Cabang</span><strong>{{ $auditLog->branch?->name ?? '-' }}</strong></div>
            <div class="info-row"><span>Action</span><strong>{{ $auditLog->action }}</strong></div>
            <div class="info-row"><span>Object</span><strong>{{ class_basename($auditLog->auditable_type ?: '-') }} #{{ $auditLog->auditable_id ?? '-' }}</strong></div>
            <div class="info-row"><span>IP Address</span><strong>{{ $auditLog->ip_address ?: '-' }}</strong></div>
        </div></x-card>
        <x-card class="xl:col-span-2" title="Deskripsi" description="Keterangan aktivitas yang tercatat."><div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-700 dark:bg-slate-800/60 dark:text-slate-200">{{ $auditLog->description ?: '-' }}</div><div class="mt-6"><h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">User Agent</h3><div class="break-words rounded-xl bg-slate-50 p-4 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">{{ $auditLog->user_agent ?: '-' }}</div></div></x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card title="Old Values" description="Data sebelum perubahan.">
            @if(!empty($auditLog->old_values))<div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>@foreach($auditLog->old_values as $key=>$value)<tr><td class="font-semibold">{{ $key }}</td><td><pre class="whitespace-pre-wrap break-words text-xs">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : ($value ?? 'null') }}</pre></td></tr>@endforeach</tbody></table></div>@else<x-empty-state title="Tidak ada old values" description="Aktivitas ini tidak memiliki data sebelumnya." />@endif
        </x-card>
        <x-card title="New Values" description="Data setelah perubahan.">
            @if(!empty($auditLog->new_values))<div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>@foreach($auditLog->new_values as $key=>$value)<tr><td class="font-semibold">{{ $key }}</td><td><pre class="whitespace-pre-wrap break-words text-xs">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : ($value ?? 'null') }}</pre></td></tr>@endforeach</tbody></table></div>@else<x-empty-state title="Tidak ada new values" description="Aktivitas ini tidak memiliki data baru." />@endif
        </x-card>
    </div>
</x-app-layout>
