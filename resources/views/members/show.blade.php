<x-app-layout>
    <x-slot name="title">Detail Anggota</x-slot>

    <x-page-header title="Detail Anggota" description="{{ $member->member_number }}">
        <x-slot name="actions">
            <a href="{{ route('members.index') }}" class="btn btn-secondary">Kembali</a>
            @if(!$member->user_id && $member->member_status === 'ACTIVE' && auth()->user()->can('user.create'))
                <a href="{{ route('members.user.create', $member) }}" class="btn btn-primary">Add to User</a>
            @endif
            @can('update', $member)
                <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">Edit Anggota</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-extrabold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">{{ $member->name }}</h2>
                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $member->member_number }}</div>
                <div class="mt-3"><x-status-badge :status="$member->member_status" /></div>
                <div class="mt-4">
                    @if($member->user_id && $member->user)
                        <a href="{{ route('users.show', $member->user) }}" class="badge badge-success">
                            User Login: {{ $member->user->email }}
                        </a>
                    @else
                        <span class="badge badge-neutral">Belum memiliki user login</span>
                    @endif
                </div>
            </div>
        </x-card>

        <x-card class="lg:col-span-2" title="Informasi Anggota" description="Detail data anggota koperasi.">
            <div class="info-list">
                <div class="info-row"><span>Cabang</span><strong>{{ $member->branch?->name ?? '-' }}</strong></div>
                <div class="info-row"><span>NIK</span><strong>{{ $member->nik ?: '-' }}</strong></div>
                <div class="info-row"><span>Jenis Kelamin</span><strong>{{ $member->gender === 'L' ? 'Laki-laki' : ($member->gender === 'P' ? 'Perempuan' : '-') }}</strong></div>
                <div class="info-row"><span>Tempat / Tanggal Lahir</span><strong>{{ $member->birth_place ?: '-' }} @if($member->birth_date) / {{ $member->birth_date->format('d/m/Y') }} @endif</strong></div>
                <div class="info-row"><span>Telepon</span><strong>{{ $member->phone ?: '-' }}</strong></div>
                <div class="info-row"><span>Email</span><strong>{{ $member->email ?: '-' }}</strong></div>
                <div class="info-row"><span>Pekerjaan</span><strong>{{ $member->occupation ?: '-' }}</strong></div>
                <div class="info-row"><span>Tanggal Bergabung</span><strong>{{ $member->join_date?->format('d/m/Y') ?? '-' }}</strong></div>
                <div class="info-row"><span>Alamat</span><strong class="max-w-md">{{ $member->address ?: '-' }}</strong></div>
                <div class="info-row"><span>Catatan</span><strong class="max-w-md">{{ $member->notes ?: '-' }}</strong></div>
            </div>
        </x-card>
    </div>
</x-app-layout>
