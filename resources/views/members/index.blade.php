<x-app-layout>
    <x-slot name="title">Anggota</x-slot>

    <x-page-header title="Master Anggota" description="Kelola data anggota koperasi.">
        @can('member.create')
            <x-slot name="actions">
                <a href="{{ route('members.create') }}" class="btn btn-primary">+ Tambah Anggota</a>
            </x-slot>
        @endcan
    </x-page-header>

    @if(session('info'))
        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
            {{ session('info') }}
        </div>
    @endif

	{{--
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Total Anggota</span><div class="stat-icon">♙</div></div><div class="stat-value">{{ $totalMembers }}</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Anggota Aktif</span><div class="stat-icon">✓</div></div><div class="stat-value">{{ $activeMembers }}</div></div>
        <div class="stat-card"><div class="stat-top"><span class="stat-label">Tidak Aktif</span><div class="stat-icon">×</div></div><div class="stat-value">{{ $inactiveMembers }}</div></div>
    </div>
	--}}

    <x-card>
        <form method="GET" action="{{ route('members.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="search" type="search" value="{{ request('search') }}"
                   placeholder="Cari nomor anggota, nama, NIK, telepon..." class="form-control">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>Aktif</option>
                <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Tidak Aktif</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Cari</button>
                <a href="{{ route('members.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Anggota</th><th>Nama</th><th>NIK</th><th>Cabang</th>
                        <th>User Login</th><th>Status</th><th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td><span class="table-primary text-indigo-600 dark:text-indigo-400">{{ $member->member_number }}</span></td>
                            <td><span class="table-primary">{{ $member->name }}</span><span class="table-secondary">{{ $member->phone ?: '-' }}</span></td>
                            <td>{{ $member->nik ?: '-' }}</td>
                            <td>{{ $member->branch?->name ?? '-' }}</td>
                            <td>
                                @if($member->user_id && $member->user)
                                    <a href="{{ route('users.show', $member->user) }}" class="badge badge-success">User Aktif</a>
                                    <span class="table-secondary">{{ $member->user->email }}</span>
                                @elseif($member->member_status === 'ACTIVE' && auth()->user()->can('user.create'))
                                    <a href="{{ route('members.user.create', $member) }}" class="btn btn-primary">Add to User</a>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td><x-status-badge :status="$member->member_status" /></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('members.show', $member) }}" class="btn btn-secondary">Detail</a>
                                    @can('update', $member)
                                        <a href="{{ route('members.edit', $member) }}" class="btn btn-secondary">Edit</a>
                                    @endcan
                                    @can('delete', $member)
                                        @if($member->member_status === 'ACTIVE')
                                            <form method="POST" action="{{ route('members.destroy', $member) }}"
                                                  class="member-deactivate-form"
                                                  data-member="{{ $member->member_number }} - {{ $member->name }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">Belum ada data anggota.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($members as $member)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $member->member_number }}</div>
                            <div class="mt-1 truncate font-semibold text-slate-900 dark:text-white">{{ $member->name }}</div>
                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $member->branch?->name ?? '-' }}</div>
                        </div>
                        <x-status-badge :status="$member->member_status" />
                    </div>

                    <div class="mt-3">
                        @if($member->user_id && $member->user)
                            <a href="{{ route('users.show', $member->user) }}" class="badge badge-success">
                                User Aktif · {{ $member->user->email }}
                            </a>
                        @elseif($member->member_status === 'ACTIVE' && auth()->user()->can('user.create'))
                            <a href="{{ route('members.user.create', $member) }}" class="btn btn-primary w-full">Add to User</a>
                        @endif
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('members.show', $member) }}" class="btn btn-secondary">Detail</a>
                        @can('update', $member)
                            <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">Edit</a>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada anggota" description="Silakan tambahkan anggota pertama." />
            @endforelse
        </div>

        @if($members->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">{{ $members->links() }}</div>
        @endif
    </x-card>

    @push('scripts')
    <script>
        document.querySelectorAll('.member-deactivate-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;
                event.preventDefault();
                window.swalConfirm({
                    icon: 'warning',
                    title: 'Nonaktifkan Anggota?',
                    text: `Anggota ${form.dataset.member} akan dinonaktifkan.`,
                    confirmButtonText: 'Ya, Nonaktifkan',
                    confirmButtonColor: '#dc2626',
                }).then((result) => { if (result.isConfirmed) form.submit(); });
            });
        });
    </script>
    @endpush
</x-app-layout>
