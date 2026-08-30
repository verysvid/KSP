<x-app-layout>
    <x-slot name="title">Detail User</x-slot>

    <x-page-header
        title="Detail User"
        description="{{ $user->email }}">

        <x-slot name="actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit User</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-extrabold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $user->name }}
                </h2>

                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $user->email }}
                </div>

                <div class="mt-3">
                    <x-status-badge :status="$user->is_active ? 'ACTIVE' : 'INACTIVE'" />
                </div>
            </div>
        </x-card>

        <x-card class="lg:col-span-2"
                title="Informasi Akun"
                description="Detail user, cabang, role dan permission.">

            <div class="info-list">
                <div class="info-row">
                    <span>Cabang</span>
                    <strong>{{ $user->branch?->name ?? 'Semua Cabang' }}</strong>
                </div>
                <div class="info-row">
                    <span>Role</span>
                    <strong>{{ $user->getRoleNames()->implode(', ') ?: '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Permission Efektif</span>
                    <strong>{{ $user->getAllPermissions()->count() }}</strong>
                </div>
                <div class="info-row">
                    <span>Dibuat</span>
                    <strong>{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">
                    Permission
                </h3>

                <div class="flex flex-wrap gap-2">
                    @forelse($user->getAllPermissions()->sortBy('name') as $permission)
                        <span class="badge badge-neutral">{{ $permission->name }}</span>
                    @empty
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            Belum ada permission.
                        </span>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
