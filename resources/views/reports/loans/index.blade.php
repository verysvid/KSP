<x-app-layout>
    <x-slot name="title">Laporan Pinjaman</x-slot>

    <x-page-header
        title="Laporan Pinjaman"
        description="Daftar pinjaman anggota yang sudah aktif atau telah lunas." />

    <div class="print:hidden">
        <x-card
            title="Filter Laporan"
            description="Gunakan filter untuk mempersempit daftar pinjaman.">

            <form method="GET" action="{{ route('reports.loans.index') }}">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                    @if(auth()->user()?->hasRole('SuperAdmin'))
                        <div>
                            <label for="branch_id" class="form-label">Cabang</label>
                            <select
                                id="branch_id"
                                name="branch_id"
                                class="form-select"
                                onchange="this.form.submit()">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option
                                        value="{{ $branch->id }}"
                                        @selected((string) request('branch_id') === (string) $branch->id)>
                                        {{ $branch->code }} - {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @unless(auth()->user()?->hasRole('Anggota'))
                        <div>
                            <label for="member_id" class="form-label">Anggota</label>
                            <select
                                id="member_id"
                                name="member_id"
                                class="form-select"
                                @disabled(auth()->user()?->hasRole('SuperAdmin') && !$selectedBranchId)>
                                <option value="">Semua Anggota</option>
                                @foreach($members as $member)
                                    <option
                                        value="{{ $member->id }}"
                                        @selected((string) request('member_id') === (string) $member->id)>
                                        {{ $member->member_number }} - {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>ACTIVE</option>
                            <option value="PAID_OFF" @selected(request('status') === 'PAID_OFF')>PAID OFF</option>
                        </select>
                    </div>

                    <div>
                        <label for="search" class="form-label">Pencarian</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="No. pinjaman / anggota / jenis">
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-5 dark:border-slate-800">
                    @if(request()->hasAny(['branch_id', 'member_id', 'status', 'search']))
                        <a href="{{ route('reports.loans.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        Tampilkan
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    @if(auth()->user()?->hasRole('Anggota') && $selectedMember)
        <div class="mt-6">
            <x-card>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Pemilik Laporan
                        </div>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                            {{ $selectedMember->name }}
                        </h2>
                        <div class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                            {{ $selectedMember->member_number }}
                            &middot;
                            {{ $selectedMember->branch->code ?? '-' }} - {{ $selectedMember->branch->name ?? '-' }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Sisa Pokok
                        </div>
                        <div class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                            Rp {{ number_format($outstandingPrincipal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-card>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Pinjaman</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalLoans) }}</div>
        </x-card>
        <x-card>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aktif</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($activeLoans) }}</div>
        </x-card>
        <x-card>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Lunas</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($paidOffLoans) }}</div>
        </x-card>
        <x-card>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sisa Pokok</div>
            <div class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($outstandingPrincipal, 0, ',', '.') }}</div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card
            title="Daftar Pinjaman"
            description="Klik Detail untuk melihat informasi pinjaman, jadwal angsuran, dan riwayat pembayaran.">

            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Pinjaman</th>
                                <th>Anggota</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th class="text-right">Nominal</th>
                                <th class="text-center">Tenor</th>
                                <th>Status</th>
                                <th class="text-right">Sisa Pokok</th>
                                <th class="text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                                <tr>
                                    <td>
                                        <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                            {{ $loan->loan_no }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900 dark:text-white">
                                            {{ $loan->member->name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $loan->member->member_number ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $loan->loanType->code ?? '-' }} - {{ $loan->loanType->name ?? '-' }}
                                    </td>
                                    <td>{{ $loan->disbursed_at?->format('d/m/Y') ?? $loan->application_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-right">
                                        Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">{{ $loan->tenor_months }} bln</td>
                                    <td><x-status-badge :status="$loan->status" /></td>
                                    <td class="text-right">
                                        <strong>Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <div class="flex justify-end">
                                            <a href="{{ route('reports.loans.show', $loan) }}" class="btn btn-secondary">
                                                Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="empty-state">
                                        Tidak ada data pinjaman yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse($loans as $loan)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $loan->loan_no }}
                                </div>
                                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                    {{ $loan->loanType->name ?? '-' }}
                                </div>
                                @unless(auth()->user()?->hasRole('Anggota'))
                                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $loan->member->member_number ?? '-' }} - {{ $loan->member->name ?? '-' }}
                                    </div>
                                @endunless
                            </div>
                            <x-status-badge :status="$loan->status" />
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Nominal</div>
                                <div class="mt-1 font-semibold">Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Sisa Pokok</div>
                                <div class="mt-1 font-semibold">Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                            <a href="{{ route('reports.loans.show', $loan) }}" class="btn btn-secondary w-full">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Tidak ada data pinjaman yang sesuai dengan filter.</div>
                @endforelse
            </div>

            @if($loans->hasPages())
                <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                    {{ $loans->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
