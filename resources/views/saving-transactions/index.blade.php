<x-app-layout>
    <x-slot name="title">Transaksi Simpanan</x-slot>

    <x-page-header
        title="Transaksi Simpanan"
        description="Kelola setoran, penarikan, dan approval transaksi simpanan.">

        @can('saving-transaction.create')
            <x-slot name="actions">
                <a href="{{ route('saving-transactions.create') }}"
                   class="btn btn-primary">
                    + Transaksi Baru
                </a>
            </x-slot>
        @endcan
    </x-page-header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Pending</span><div class="stat-icon">!</div></div>
            <div class="stat-value">{{ $pendingCount }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Approved</span><div class="stat-icon">✓</div></div>
            <div class="stat-value">{{ $approvedCount }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Rejected</span><div class="stat-icon">×</div></div>
            <div class="stat-value">{{ $rejectedCount }}</div>
        </div>
    </div>

    <x-card>
        <form method="GET"
              action="{{ route('saving-transactions.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 xl:grid-cols-[1fr_160px_220px_160px_160px_auto]">

            <input type="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari no transaksi / anggota..."
                   class="form-control">

            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                @foreach(['PENDING','APPROVED','REJECTED'] as $status)
                    <option value="{{ $status }}"
                            @selected(request('status') === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <select name="saving_type_id" class="form-select">
                <option value="">Semua Jenis Simpanan</option>
                @foreach($savingTypes as $type)
                    <option value="{{ $type->id }}"
                            @selected((string) request('saving_type_id') === (string) $type->id)>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="date_from"
                   value="{{ request('date_from') }}"
                   class="form-control">

            <input type="date" name="date_to"
                   value="{{ request('date_to') }}"
                   class="form-control">

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Filter</button>
                <a href="{{ route('saving-transactions.index') }}"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th>Mutasi</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $transaction->trx_no }}
                                </span>
                                <span class="table-secondary">{{ $transaction->period }}</span>
                            </td>
                            <td>{{ $transaction->transaction_date?->format('d/m/Y') }}</td>
                            <td>
                                <span class="table-primary">{{ $transaction->member?->name ?? '-' }}</span>
                                <span class="table-secondary">{{ $transaction->member?->member_number ?? '-' }}</span>
                            </td>
                            <td>{{ $transaction->savingType?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $transaction->credit > 0 ? 'badge-success' : 'badge-warning' }}">
                                    {{ $transaction->mutation_type }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$transaction->status" /></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('saving-transactions.show', $transaction) }}"
                                       class="btn btn-secondary">Detail</a>

                                    @can('approve', $transaction)
										<a
											href="{{ route('saving-transactions.show', $transaction) }}"
											class="btn btn-primary">
											Review & Approve
										</a>

                                        {{-- <form method="POST"
                                              action="{{ route('saving-transactions.approve', $transaction) }}"
                                              class="approve-saving-transaction-form"
                                              data-transaction-no="{{ $transaction->trx_no }}"
                                              data-member-name="{{ $transaction->member?->name }}"
                                              data-amount="Rp {{ number_format($transaction->amount, 0, ',', '.') }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary">Approve</button>
                                        </form> --}}
                                    @endcan


                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">Belum ada transaksi simpanan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($transactions as $transaction)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $transaction->trx_no }}</div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $transaction->member?->name ?? '-' }}</div>
                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $transaction->savingType?->name ?? '-' }} · {{ $transaction->mutation_type }}
                            </div>
                        </div>
                        <x-status-badge :status="$transaction->status" />
                    </div>

                    <div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('saving-transactions.show', $transaction) }}"
                           class="btn btn-secondary w-full">Detail</a>
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada transaksi simpanan"
                               description="Transaksi baru akan tampil di sini." />
            @endforelse
        </div>

        @if($transactions->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
    <script>
        document.querySelectorAll('.approve-saving-transaction-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;

                event.preventDefault();

                window.swalConfirm({
                    icon: 'question',
                    title: 'Approve Transaksi?',
                    html:
                        'Apakah Anda yakin ingin menyetujui transaksi ini?<br><br>' +
                        '<div style="text-align:left">' +
                        '<strong>No. Transaksi:</strong> ' + form.dataset.transactionNo + '<br>' +
                        '<strong>Anggota:</strong> ' + form.dataset.memberName + '<br>' +
                        '<strong>Nominal:</strong> ' + form.dataset.amount +
                        '</div>',
                    confirmButtonText: 'Ya, Approve',
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
