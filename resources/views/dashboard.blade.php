<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <x-page-header
        title="Dashboard"
        description="Ringkasan aktivitas dan data utama aplikasi koperasi."
    />

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Anggota</span>
                <div class="stat-icon">♙</div>
            </div>
            <div class="stat-value">{{ $totalMembers ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Anggota Aktif</span>
                <div class="stat-icon">✓</div>
            </div>
            <div class="stat-value">{{ $activeMembers ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Simpanan</span>
                <div class="stat-icon">Rp</div>
            </div>
            <div class="stat-value">
                Rp {{ number_format($totalSavings ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Transaksi Pending</span>
                <div class="stat-icon">!</div>
            </div>
            <div class="stat-value">{{ $pendingTransactions ?? 0 }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <x-card title="Transaksi Terbaru"
                description="Aktivitas transaksi simpanan terbaru.">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($latestTransactions ?? []) as $transaction)
                        <tr>
                            <td>
                                <span class="table-primary">{{ $transaction->trx_no }}</span>
                                <span class="table-secondary">
                                    {{ optional($transaction->transaction_date)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>{{ $transaction->member->name ?? '-' }}</td>
                            <td>{{ $transaction->savingType->name ?? '-' }}</td>
                            <td>Rp {{ number_format(($transaction->credit ?: $transaction->debit), 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$transaction->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Informasi Sistem"
                description="Status aplikasi koperasi.">
            <div class="info-list">
			{{--
                <div class="info-row">
                    <span>Laravel</span>
                    <strong>{{ app()->version() }}</strong>
                </div>
			--}}
                <div class="info-row">
                    <span>Login Sebagai</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
                <div class="info-row">
                    <span>Role</span>
                    <strong>{{ auth()->user()->getRoleNames()->first() ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span>Cabang</span>
                    <strong>{{ auth()->user()->branch->name ?? 'Semua Cabang' }}</strong>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
