<x-app-layout>
    <x-slot name="title">Jenis Simpanan</x-slot>

    <x-page-header
        title="Master Jenis Simpanan"
        description="Kelola jenis simpanan koperasi.">

        @can('saving-type.create')
            <x-slot name="actions">
                <a href="{{ route('saving-types.create') }}"
                   class="btn btn-primary">
                    + Tambah Jenis Simpanan
                </a>
            </x-slot>
        @endcan
    </x-page-header>

	{{--
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Jenis</span>
                <div class="stat-icon">▣</div>
            </div>
            <div class="stat-value">{{ $totalTypes }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jenis Aktif</span>
                <div class="stat-icon">✓</div>
            </div>
            <div class="stat-value">{{ $activeTypes }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jenis Wajib</span>
                <div class="stat-icon">!</div>
            </div>
            <div class="stat-value">{{ $mandatoryTypes }}</div>
        </div>
    </div>
	--}}

    <x-card>
        <form
            method="GET"
            action="{{ route('saving-types.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_auto]">

            <input
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari kode, nama, deskripsi..."
                class="form-control">

            <select
                name="status"
                class="form-select">
                <option value="">Semua Status</option>
                <option value="active"
                        @selected(request('status') === 'active')}>
                    Aktif
                </option>
                <option value="inactive"
                        @selected(request('status') === 'inactive')}>
                    Tidak Aktif
                </option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="btn btn-primary flex-1">
                    Cari
                </button>

                <a href="{{ route('saving-types.index') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        {{-- Desktop --}}
        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Nominal</th>
                        <th>Wajib</th>
                        <th>Dapat Ditarik</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($savingTypes as $savingType)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $savingType->code }}
                                </span>
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $savingType->name }}
                                </span>

                                <span class="table-secondary">
                                    {{ $savingType->description ?: '-' }}
                                </span>
                            </td>

                            <td>
                                @if($savingType->amount !== null)
                                    Rp {{ number_format($savingType->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $savingType->is_mandatory ? 'badge-warning' : 'badge-neutral' }}">
                                    {{ $savingType->is_mandatory ? 'YA' : 'TIDAK' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $savingType->is_withdrawable ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $savingType->is_withdrawable ? 'YA' : 'TIDAK' }}
                                </span>
                            </td>

                            <td>
                                <x-status-badge
                                    :status="$savingType->is_active ? 'ACTIVE' : 'INACTIVE'" />
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('saving-types.show', $savingType) }}"
                                       class="btn btn-secondary">
                                        Detail
                                    </a>

                                    @can('update', $savingType)
                                        <a href="{{ route('saving-types.edit', $savingType) }}"
                                           class="btn btn-secondary">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('saving-types.toggle-status', $savingType) }}"
                                            class="saving-type-status-form"
                                            data-name="{{ $savingType->name }}"
                                            data-active="{{ $savingType->is_active ? '1' : '0' }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn {{ $savingType->is_active ? 'btn-danger' : 'btn-primary' }}">
                                                {{ $savingType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
									
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="empty-state">
                                Belum ada jenis simpanan.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @forelse($savingTypes as $savingType)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $savingType->code }}
                            </div>

                            <div class="mt-1 truncate font-semibold text-slate-900 dark:text-white">
                                {{ $savingType->name }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                @if($savingType->amount !== null)
                                    Rp {{ number_format($savingType->amount, 0, ',', '.') }}
                                @else
                                    Nominal fleksibel
                                @endif
                            </div>
                        </div>

                        <x-status-badge
                            :status="$savingType->is_active ? 'ACTIVE' : 'INACTIVE'" />
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="badge {{ $savingType->is_mandatory ? 'badge-warning' : 'badge-neutral' }}">
                            {{ $savingType->is_mandatory ? 'Wajib' : 'Tidak Wajib' }}
                        </span>

                        <span class="badge {{ $savingType->is_withdrawable ? 'badge-success' : 'badge-neutral' }}">
                            {{ $savingType->is_withdrawable ? 'Dapat Ditarik' : 'Tidak Dapat Ditarik' }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('saving-types.show', $savingType) }}"
                           class="btn btn-secondary">
                            Detail
                        </a>

                        @can('update', $savingType)
                            <a href="{{ route('saving-types.edit', $savingType) }}"
                               class="btn btn-primary">
                                Edit
                            </a>
                        @endcan

                    </div>
                </div>
            @empty
                <x-empty-state
                    title="Belum ada jenis simpanan"
                    description="Silakan tambahkan jenis simpanan pertama." />
            @endforelse
        </div>

        @if($savingTypes->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5
                        dark:border-slate-800">
                {{ $savingTypes->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script>
            document.querySelectorAll('.saving-type-status-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        const active = form.dataset.active === '1';

                        window.swalConfirm({
                            icon: active ? 'warning' : 'question',
                            title: active
                                ? 'Nonaktifkan Jenis Simpanan?'
                                : 'Aktifkan Jenis Simpanan?',
                            text: active
                                ? `Jenis simpanan ${form.dataset.name} akan dinonaktifkan.`
                                : `Jenis simpanan ${form.dataset.name} akan diaktifkan kembali.`,
                            confirmButtonText: active
                                ? 'Ya, Nonaktifkan'
                                : 'Ya, Aktifkan',
                            confirmButtonColor: active
                                ? '#dc2626'
                                : '#4f46e5',
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
