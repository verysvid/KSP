<x-app-layout>
    <x-slot name="title">Cabang</x-slot>

    <x-page-header
        title="Master Cabang"
        description="Kelola data cabang koperasi.">

        @can('branch.create')
            <x-slot name="actions">
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    + Tambah Cabang
                </a>
            </x-slot>
        @endcan
    </x-page-header>

	{{--
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Cabang</span>
                <div class="stat-icon">⌘</div>
            </div>
            <div class="stat-value">{{ $totalBranches }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Cabang Aktif</span>
                <div class="stat-icon">✓</div>
            </div>
            <div class="stat-value">{{ $activeBranches }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Tidak Aktif</span>
                <div class="stat-icon">×</div>
            </div>
            <div class="stat-value">{{ $inactiveBranches }}</div>
        </div>
    </div>
	--}}

    <x-card>
        <form method="GET"
              action="{{ route('branches.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_auto]">

            <input
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari kode, nama, kepala cabang..."
                class="form-control">

            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="1" @selected(request('status') === '1')>Aktif</option>
                <option value="0" @selected(request('status') === '0')>Tidak Aktif</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Cari</button>
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        {{-- Desktop --}}
        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Cabang</th>
                        <th>Kepala Cabang</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $branch->code }}
                                </span>
                            </td>

                            <td>
                                <span class="table-primary">{{ $branch->name }}</span>
                                <span class="table-secondary">{{ $branch->address ?: '-' }}</span>
                            </td>

                            <td>{{ $branch->manager_name ?: '-' }}</td>

                            <td>
                                <span class="table-primary">{{ $branch->phone ?: '-' }}</span>
                                <span class="table-secondary">{{ $branch->email ?: '-' }}</span>
                            </td>

                            <td>
                                <x-status-badge :status="$branch->is_active ? 'ACTIVE' : 'INACTIVE'" />
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('branches.show', $branch) }}"
                                       class="btn btn-secondary">
                                        Detail
                                    </a>

                                    @can('branch.edit')
                                        <a href="{{ route('branches.edit', $branch) }}"
                                           class="btn btn-secondary">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('branches.toggle-status', $branch) }}"
                                              class="branch-status-form"
                                              data-name="{{ $branch->name }}"
                                              data-active="{{ $branch->is_active ? '1' : '0' }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn {{ $branch->is_active ? 'btn-danger' : 'btn-primary' }}">
                                                {{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                Belum ada data cabang.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @forelse($branches as $branch)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $branch->code }}
                            </div>

                            <div class="mt-1 truncate font-semibold text-slate-900 dark:text-white">
                                {{ $branch->name }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $branch->manager_name ?: 'Kepala cabang belum ditentukan' }}
                            </div>
                        </div>

                        <x-status-badge :status="$branch->is_active ? 'ACTIVE' : 'INACTIVE'" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('branches.show', $branch) }}"
                           class="btn btn-secondary">
                            Detail
                        </a>

                        @can('branch.edit')
                            <a href="{{ route('branches.edit', $branch) }}"
                               class="btn btn-primary">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state
                    title="Belum ada cabang"
                    description="Silakan tambahkan cabang pertama." />
            @endforelse
        </div>

        @if($branches->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $branches->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script>
            document.querySelectorAll('.branch-status-form').forEach((form) => {
                form.addEventListener('submit', function (event) {
                    if (!window.swalConfirm) {
                        return;
                    }

                    event.preventDefault();

                    const active = form.dataset.active === '1';
                    const name = form.dataset.name || '';

                    window.swalConfirm({
                        icon: active ? 'warning' : 'question',
                        title: active ? 'Nonaktifkan Cabang?' : 'Aktifkan Cabang?',
                        text: active
                            ? `Cabang ${name} akan dinonaktifkan.`
                            : `Cabang ${name} akan diaktifkan kembali.`,
                        confirmButtonText: active
                            ? 'Ya, Nonaktifkan'
                            : 'Ya, Aktifkan',
                        confirmButtonColor: active ? '#dc2626' : '#4f46e5',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
