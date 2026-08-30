<x-app-layout>
    <x-slot name="title">Pembayaran Angsuran</x-slot>

    <x-page-header
        title="Pembayaran Angsuran"
        description="Riwayat pembayaran angsuran pinjaman anggota." />

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Pembayaran</span>
                <div class="stat-icon">▣</div>
            </div>
            <div class="stat-value">
                {{ number_format($totalPayments, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Diterima</span>
                <div class="stat-icon">Rp</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalAmount, 0, ',', '.') }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pelunasan Pokok</span>
                <div class="stat-icon">P</div>
            </div>
            <div class="stat-value text-xl">
                Rp {{ number_format($totalPrincipal, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <x-card>
        <form
            method="GET"
            action="{{ route('loan-payments.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[minmax(240px,1fr)_160px_160px_190px_auto]">

            <input
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari pembayaran, pinjaman, anggota..."
                class="form-control">

            <input
                name="date_from"
                type="date"
                value="{{ request('date_from') }}"
                class="form-control">

            <input
                name="date_to"
                type="date"
                value="{{ request('date_to') }}"
                class="form-control">

            @if(auth()->user()?->hasRole('SuperAdmin'))
                <select name="branch_id" class="form-select">
                    <option value="">Semua Cabang</option>

                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected((string) request('branch_id') === (string) $branch->id)>
                            {{ $branch->code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <div class="hidden md:block"></div>
            @endif

            <div class="flex gap-2">
                <button type="submit"
                        class="btn btn-primary flex-1">
                    Cari
                </button>

                <a href="{{ route('loan-payments.index') }}"
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
                        <th>No. Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Pinjaman / Anggota</th>
                        <th>Angsuran</th>
                        <th>Kas / Bank</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $payment->payment_no }}
                                </span>

                                <span class="table-secondary">
                                    {{ $payment->reference_no ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $payment->payment_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $payment->loan->loan_no ?? '-' }}
                                </span>

                                <span class="table-secondary">
                                    {{ $payment->loan->member->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                #{{ $payment->installment->installment_no ?? '-' }}
                            </td>

                            <td>
                                {{ $payment->cashAccount->code ?? '-' }}
                                -
                                {{ $payment->cashAccount->name ?? '-' }}
                            </td>

                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
                                </strong>
                            </td>

                            <td>
                                <div class="flex justify-end">
                                    <a href="{{ route('loans.show', $payment->loan_id) }}"
                                       class="btn btn-secondary">
                                        Detail Pinjaman
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada pembayaran angsuran.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($payments as $payment)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $payment->payment_no }}
                    </div>

                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $payment->loan->member->name ?? '-' }}
                    </div>

                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $payment->loan->loan_no ?? '-' }}
                        · Angsuran #{{ $payment->installment->installment_no ?? '-' }}
                    </div>

                    <div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('loans.show', $payment->loan_id) }}"
                           class="btn btn-secondary w-full">
                            Detail Pinjaman
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state
                    title="Belum ada pembayaran"
                    description="Riwayat pembayaran angsuran akan muncul di sini." />
            @endforelse
        </div>

        @if($payments->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
                {{ $payments->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
