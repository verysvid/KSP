<x-app-layout>
    <x-slot name="title">User</x-slot>

    <x-page-header
        title="Master User"
        description="Kelola akun login, role, cabang, dan status user.">

        @can('user.create')
            <x-slot name="actions">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    + Tambah User
                </a>
            </x-slot>
        @endcan
    </x-page-header>

    @if(session('info'))
        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700
                    dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->has('user'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                    dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
            {{ $errors->first('user') }}
        </div>
    @endif

	{{--
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total User</span>
                <div class="stat-icon">♚</div>
            </div>
            <div class="stat-value">{{ $totalUsers }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">User Aktif</span>
                <div class="stat-icon">✓</div>
            </div>
            <div class="stat-value">{{ $activeUsers }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Tidak Aktif</span>
                <div class="stat-icon">×</div>
            </div>
            <div class="stat-value">{{ $inactiveUsers }}</div>
        </div>
    </div>
	--}}

    <x-card>
        <form method="GET"
              action="{{ route('users.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_170px_180px_auto]">

            <input name="search"
                   type="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama atau email..."
                   class="form-control">

            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Tidak Aktif</option>
            </select>

            <select name="role" class="form-select">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                            @selected(request('role') === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Cari</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Cabang</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <span class="table-primary">{{ $user->name }}</span>
                                <span class="table-secondary">{{ $user->email }}</span>
                            </td>
                            <td>{{ $user->branch?->name ?? 'Semua Cabang' }}</td>
                            <td>
                                <span class="badge badge-neutral">
                                    {{ $user->getRoleNames()->first() ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <x-status-badge :status="$user->is_active ? 'ACTIVE' : 'INACTIVE'" />
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                                        Detail
                                    </a>

                                    @can('update', $user)
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">
                                            Edit
                                        </a>
                                    @endcan

                                    @if($user->is_active)
                                        @can('delete', $user)
                                            <form method="POST"
                                                  action="{{ route('users.destroy', $user) }}"
                                                  class="user-status-form"
                                                  data-name="{{ $user->name }}"
                                                  data-mode="deactivate">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('restore', $user)
                                            <form method="POST"
                                                  action="{{ route('users.restore', $user) }}"
                                                  class="user-status-form"
                                                  data-name="{{ $user->name }}"
                                                  data-mode="activate">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-primary">Aktifkan</button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada data user.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($users as $user)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate font-semibold text-slate-900 dark:text-white">
                                {{ $user->name }}
                            </div>
                            <div class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">
                                {{ $user->email }}
                            </div>
                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $user->branch?->name ?? 'Semua Cabang' }}
                                • {{ $user->getRoleNames()->first() ?? '-' }}
                            </div>
                        </div>
                        <x-status-badge :status="$user->is_active ? 'ACTIVE' : 'INACTIVE'" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                            Detail
                        </a>
                        @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada user" description="Silakan tambahkan user pertama." />
            @endforelse
        </div>

        @if($users->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
    <script>
        document.querySelectorAll('.user-status-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;

                event.preventDefault();

                const activating = form.dataset.mode === 'activate';

                window.swalConfirm({
                    icon: activating ? 'question' : 'warning',
                    title: activating ? 'Aktifkan User?' : 'Nonaktifkan User?',
                    text: activating
                        ? `User ${form.dataset.name} akan diaktifkan kembali.`
                        : `User ${form.dataset.name} tidak dapat login setelah dinonaktifkan.`,
                    confirmButtonText: activating ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan',
                    confirmButtonColor: activating ? '#4f46e5' : '#dc2626',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
