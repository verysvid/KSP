<x-app-layout>
    <x-slot name="title">Dashboard Pinjaman</x-slot>

    <x-page-header
        title="Dashboard Pinjaman"
        description="Ringkasan outstanding, jatuh tempo, dan tunggakan pinjaman.">

        <x-slot name="actions">
            @if(Route::has('loans.overdue.refresh'))
                <form method="POST" action="{{ route('loans.overdue.refresh') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary">Refresh Overdue</button>
                </form>
            @endif

            <a href="{{ route('loans.index') }}" class="btn btn-primary">Daftar Pinjaman</a>
        </x-slot>
    </x-page-header>

    @if(auth()->user()?->hasRole('SuperAdmin'))
        <x-card class="mb-5">
            <form method="GET" action="{{ route('loan-dashboard.index') }}"
                  class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="branch_id" class="form-label">Cabang</label>
                    <select id="branch_id" name="branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected((string) $selectedBranchId === (string) $branch->id)>
                                {{ $branch->code }} - {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('loan-dashboard.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </x-card>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Pinjaman Aktif</span><div class="stat-icon">P</div></div>
            <div class="stat-value">{{ number_format($activeLoans, 0, ',', '.') }}</div>
            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Lunas: {{ number_format($paidOffLoans, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Outstanding Pokok</span><div class="stat-icon">Rp</div></div>
            <div class="stat-value text-xl">Rp {{ number_format($outstandingPrincipal, 0, ',', '.') }}</div>
            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Bunga: Rp {{ number_format($outstandingInterest, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Angsuran Overdue</span><div class="stat-icon">!</div></div>
            <div class="stat-value">{{ number_format($overdueInstallments, 0, ',', '.') }}</div>
            <div class="mt-2 text-sm text-red-600 dark:text-red-400">Rp {{ number_format($overdueAmount, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Jatuh Tempo 7 Hari</span><div class="stat-icon">7</div></div>
            <div class="stat-value">{{ number_format($dueNextSevenDays, 0, ',', '.') }}</div>
            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Per {{ $today->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <x-card title="Aging Tunggakan" description="Distribusi angsuran berdasarkan umur keterlambatan.">
            <div class="space-y-3">
                @php
                    $agingRows = [
                        ['Lancar / Belum Jatuh Tempo', $aging['current']],
                        ['Terlambat 1 - 30 Hari', $aging['1_30']],
                        ['Terlambat 31 - 60 Hari', $aging['31_60']],
                        ['Terlambat 61 - 90 Hari', $aging['61_90']],
                        ['Terlambat > 90 Hari', $aging['over_90']],
                    ];
                @endphp
                @foreach($agingRows as [$label, $value])
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 dark:border-slate-700">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($value, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Jatuh Tempo 7 Hari" description="Angsuran yang perlu segera ditindaklanjuti.">
            <div class="space-y-3">
                @forelse($upcomingList as $installment)
                    <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $installment->loan->member->name ?? '-' }}</div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $installment->loan->loan_no ?? '-' }} · Angsuran #{{ $installment->installment_no }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $installment->due_date?->format('d/m/Y') }}</div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada angsuran jatuh tempo dalam 7 hari ke depan.</div>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="mt-5">
        <x-card title="Tunggakan Terlama" description="10 angsuran overdue paling lama.">
            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Anggota</th><th>No. Pinjaman</th><th>Angsuran</th><th>Jatuh Tempo</th>
                                <th>Hari Terlambat</th><th>Penalty</th><th class="text-right">Tagihan</th><th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdueList as $installment)
                                @php
                                    $daysLate = $installment->due_date ? $installment->due_date->copy()->startOfDay()->diffInDays($today) : 0;
                                    $remainingAmount = ((float) $installment->principal_amount - (float) $installment->principal_paid)
                                        + ((float) $installment->interest_amount - (float) $installment->interest_paid)
                                        + ((float) $installment->penalty_amount - (float) $installment->penalty_paid);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="table-primary">{{ $installment->loan->member->name ?? '-' }}</span>
                                        @if(auth()->user()?->hasRole('SuperAdmin'))
                                            <span class="table-secondary">{{ $installment->loan->branch->name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $installment->loan->loan_no ?? '-' }}</td>
                                    <td>#{{ $installment->installment_no }}</td>
                                    <td>{{ $installment->due_date?->format('d/m/Y') }}</td>
                                    <td><span class="font-semibold text-red-600 dark:text-red-400">{{ $daysLate }} hari</span></td>
                                    <td>{{ $installment->loan->loanType->penalty_type ?? 'NONE' }}</td>
                                    <td class="text-right"><strong>Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong></td>
                                    <td><div class="flex justify-end"><a href="{{ route('loans.show', $installment->loan_id) }}" class="btn btn-secondary">Detail</a></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="empty-state">Tidak ada angsuran overdue.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse($overdueList as $installment)
                    @php
                        $daysLate = $installment->due_date ? $installment->due_date->copy()->startOfDay()->diffInDays($today) : 0;
                        $remainingAmount = ((float) $installment->principal_amount - (float) $installment->principal_paid)
                            + ((float) $installment->interest_amount - (float) $installment->interest_paid)
                            + ((float) $installment->penalty_amount - (float) $installment->penalty_paid);
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="font-semibold text-slate-900 dark:text-white">{{ $installment->loan->member->name ?? '-' }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $installment->loan->loan_no ?? '-' }} · Angsuran #{{ $installment->installment_no }}</div>
                        <div class="mt-3 text-sm font-semibold text-red-600 dark:text-red-400">{{ $daysLate }} hari terlambat</div>
                        <div class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</div>
                        <div class="mt-4"><a href="{{ route('loans.show', $installment->loan_id) }}" class="btn btn-secondary w-full">Detail Pinjaman</a></div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada angsuran overdue.</div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-layout>
