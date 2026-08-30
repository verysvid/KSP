<x-app-layout>
    <x-slot name="title">Role & Permission</x-slot>

    <x-page-header
        title="Role & Permission"
        description="Kelola permission yang dimiliki setiap role." />

    <x-card>
        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Role</th>
                        <th>Jumlah Permission</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <span class="table-primary">{{ $role->name }}</span>
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>
                                <div class="flex justify-end">
                                    @can('role.edit')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-secondary">
                                            Atur Permission
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">Belum ada role.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach($roles as $role)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $role->name }}</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $role->permissions_count }} permission
                    </div>
                    @can('role.edit')
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary mt-3 w-full">
                            Atur Permission
                        </a>
                    @endcan
                </div>
            @endforeach
        </div>
    </x-card>
</x-app-layout>
