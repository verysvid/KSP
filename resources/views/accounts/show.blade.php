<x-app-layout>
    <x-slot name="title">Detail Akun</x-slot>
    <x-page-header title="Detail Akun" description="{{ $account->code }} - {{ $account->name }}">
        <x-slot name="actions"><a href="{{ route('accounts.index') }}" class="btn btn-secondary">Kembali</a>@can('account.edit')<a href="{{ route('accounts.edit',$account) }}" class="btn btn-primary">Edit</a>@endcan</x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card><div class="text-center"><div class="text-sm text-slate-500 dark:text-slate-400">Kode Akun</div><div class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ $account->code }}</div><div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">{{ $account->name }}</div></div></x-card>
        <x-card class="lg:col-span-2" title="Informasi Akun" description="Struktur dan konfigurasi akun.">
            <div class="info-list">
                <div class="info-row"><span>Tipe</span><strong>{{ $account->type_label }}</strong></div>
                <div class="info-row"><span>Parent</span><strong>{{ $account->parent ? $account->parent->code.' - '.$account->parent->name : '-' }}</strong></div>
                <div class="info-row"><span>Normal Balance</span><strong>{{ $account->normal_balance_label }}</strong></div>
                <div class="info-row"><span>Urutan</span><strong>{{ $account->sort_order }}</strong></div>
                <div class="info-row"><span>Kas / Bank</span><strong>{{ $account->is_cash_bank ? 'Ya' : 'Tidak' }}</strong></div>
                <div class="info-row"><span>Posting Account</span><strong>{{ $account->is_postable ? 'Ya' : 'Tidak' }}</strong></div>
                <div class="info-row"><span>Status</span><strong>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</strong></div>
                <div class="info-row"><span>Deskripsi</span><strong class="max-w-md">{{ $account->description ?: '-' }}</strong></div>
            </div>
        </x-card>
    </div>

    @if($account->children->isNotEmpty())
        <div class="mt-6"><x-card title="Sub Akun" description="Daftar akun yang berada di bawah akun ini."><div class="space-y-3">@foreach($account->children as $child)<div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-700"><div><div class="font-bold text-slate-900 dark:text-white">{{ $child->code }} - {{ $child->name }}</div><div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $child->is_postable ? 'Posting Account' : 'Account Header' }}</div></div><a href="{{ route('accounts.show',$child) }}" class="btn btn-secondary">Detail</a></div>@endforeach</div></x-card></div>
    @endif
</x-app-layout>
