<x-app-layout>
    <x-slot name="title">Chart of Accounts</x-slot>

    <x-page-header
        title="Chart of Accounts"
        description="Master akun untuk seluruh transaksi Simpanan, Pinjaman, dan laporan keuangan.">

        <x-slot name="actions">
            @can('account.create')
                <a href="{{ route('accounts.create') }}"
                   class="btn btn-primary">
                    + Tambah Akun
                </a>
            @endcan
        </x-slot>
    </x-page-header>


    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Akun</span>
                <div class="stat-icon">#</div>
            </div>
            <div class="stat-value">
                {{ number_format($totalCount, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Akun Aktif</span>
                <div class="stat-icon">A</div>
            </div>
            <div class="stat-value">
                {{ number_format($activeCount, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Posting Account</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value">
                {{ number_format($postableCount, 0, ',', '.') }}
            </div>
        </div>
    </div>


    <x-card>
        <form
            method="GET"
            action="{{ route('accounts.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_180px_180px_auto] md:items-end">

            <div class="min-w-0 flex-1">
                <label for="search" class="form-label">Pencarian</label>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Kode / nama akun"
                >
            </div>

            <div>
                <label for="type" class="form-label">Tipe</label>

                <select
                    id="type"
                    name="type"
                    class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="ASSET" @selected(request('type') === 'ASSET')>
                        Aset
                    </option>
                    <option value="LIABILITY" @selected(request('type') === 'LIABILITY')>
                        Liabilitas
                    </option>
                    <option value="EQUITY" @selected(request('type') === 'EQUITY')>
                        Ekuitas
                    </option>
                    <option value="REVENUE" @selected(request('type') === 'REVENUE')>
                        Pendapatan
                    </option>
                    <option value="EXPENSE" @selected(request('type') === 'EXPENSE')>
                        Beban
                    </option>
                </select>
            </div>

            <div>
                <label for="postable" class="form-label">Posting</label>

                <select
                    id="postable"
                    name="postable"
                    class="form-select">
                    <option value="">Semua</option>
                    <option value="yes" @selected(request('postable') === 'yes')>
                        Posting
                    </option>
                    <option value="no" @selected(request('postable') === 'no')>
                        Header
                    </option>
                </select>
            </div>

            <div>
                <label for="status" class="form-label">Status</label>

                <select
                    id="status"
                    name="status"
                    class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active"
                            @selected(request('status') === 'active')>
                        Aktif
                    </option>
                    <option value="inactive"
                            @selected(request('status') === 'inactive')>
                        Nonaktif
                    </option>
                </select>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="btn btn-primary">
                    Cari
                </button>

                <a
                    href="{{ route('accounts.index') }}"
                    class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Saldo Normal</th>
                        <th>Parent</th>
                        <th>Posting</th>
                        <th>Kas/Bank</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <span class="table-primary">
                                    {{ $account->code }}
                                </span>
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $account->name }}
                                </span>

                                @if(!$account->is_postable)
                                    <span class="table-secondary">
                                        Account Header
                                    </span>
                                @elseif($account->description)
                                    <span class="table-secondary">
                                        {{ $account->description }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ match($account->type) {
                                    'ASSET' => 'Aset',
                                    'LIABILITY' => 'Liabilitas',
                                    'EQUITY' => 'Ekuitas',
                                    'REVENUE' => 'Pendapatan',
                                    'EXPENSE' => 'Beban',
                                    default => $account->type,
                                } }}
                            </td>

                            <td>
                                {{ $account->normal_balance === 'CREDIT'
                                    ? 'Kredit'
                                    : 'Debit' }}
                            </td>

                            <td>
                                {{ $account->parent
                                    ? $account->parent->code . ' - ' . $account->parent->name
                                    : '-' }}
                            </td>

                            <td>
                                @if($account->is_postable)
                                    <span class="status-badge status-active">
                                        Posting
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        Header
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $account->is_cash_bank ? 'Ya' : '-' }}
                            </td>

                            <td>
                                <span class="status-badge {{ $account->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('accounts.show', $account) }}"
                                        class="btn btn-secondary">
                                        Detail
                                    </a>

                                    @can('account.edit')
                                        <a
                                            href="{{ route('accounts.edit', $account) }}"
                                            class="btn btn-secondary">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('accounts.toggle-status', $account) }}"
                                            class="account-toggle-form"
                                            data-account="{{ $account->code }} - {{ $account->name }}"
                                            data-action="{{ $account->is_active ? 'menonaktifkan' : 'mengaktifkan' }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-secondary">
                                                {{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="9"
                                class="empty-state">
                                Tidak ada akun.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($accounts as $account)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $account->code }}
                            </div>

                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                {{ $account->name }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ match($account->type) {
                                    'ASSET' => 'Aset',
                                    'LIABILITY' => 'Liabilitas',
                                    'EQUITY' => 'Ekuitas',
                                    'REVENUE' => 'Pendapatan',
                                    'EXPENSE' => 'Beban',
                                    default => $account->type,
                                } }}
                                ·
                                {{ $account->normal_balance === 'CREDIT'
                                    ? 'Kredit'
                                    : 'Debit' }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Parent:
                                {{ $account->parent
                                    ? $account->parent->code . ' - ' . $account->parent->name
                                    : '-' }}
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <span class="status-badge {{ $account->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>

                            @if($account->is_postable)
                                <span class="status-badge status-active">
                                    Posting
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    Header
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <a
                            href="{{ route('accounts.show', $account) }}"
                            class="btn btn-secondary flex-1">
                            Detail
                        </a>

                        @can('account.edit')
                            <a
                                href="{{ route('accounts.edit', $account) }}"
                                class="btn btn-secondary flex-1">
                                Edit
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada akun.
                </div>
            @endforelse
        </div>

        @if($accounts->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $accounts->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.account-toggle-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (typeof window.swalConfirm !== 'function') {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Konfirmasi',
                            html:
                                'Apakah Anda yakin ingin ' +
                                form.dataset.action +
                                ' akun <strong>' +
                                form.dataset.account +
                                '</strong>?',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal',
                            showCancelButton: true
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
