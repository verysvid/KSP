<x-app-layout>
    <x-slot name="title">Detail Cabang</x-slot>

    <x-page-header
        title="Detail Cabang"
        description="{{ $branch->code }} - {{ $branch->name }}">

        <x-slot name="actions">
            <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                Kembali
            </a>

            @can('branch.edit')
                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-primary">
                    Edit Cabang
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-100 text-2xl font-extrabold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($branch->code, 0, 2)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $branch->name }}
                </h2>

                <div class="mt-2">
                    <x-status-badge :status="$branch->is_active ? 'ACTIVE' : 'INACTIVE'" />
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                    <div class="text-xs text-slate-500 dark:text-slate-400">User</div>
                    <div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                        {{ $branch->users_count }}
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                    <div class="text-xs text-slate-500 dark:text-slate-400">Anggota</div>
                    <div class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                        {{ $branch->members_count }}
                    </div>
                </div>
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Informasi Cabang"
            description="Detail informasi cabang koperasi.">

            <div class="info-list">
                <div class="info-row">
                    <span>Kode Cabang</span>
                    <strong>{{ $branch->code }}</strong>
                </div>

                <div class="info-row">
                    <span>Nama Cabang</span>
                    <strong>{{ $branch->name }}</strong>
                </div>

                <div class="info-row">
                    <span>Kepala Cabang</span>
                    <strong>{{ $branch->manager_name ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Telepon</span>
                    <strong>{{ $branch->phone ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Email</span>
                    <strong>{{ $branch->email ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Alamat</span>
                    <strong class="max-w-md">{{ $branch->address ?: '-' }}</strong>
                </div>
            </div>
        </x-card>

    </div>
</x-app-layout>
