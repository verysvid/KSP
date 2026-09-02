<x-app-layout>
    <x-slot name="title">Laporan Simpanan</x-slot>

    <x-page-header
        title="Laporan Simpanan"
        description="Rekening koran simpanan anggota berdasarkan periode transaksi.">
        @if($selectedMember)
            <x-slot name="actions">
                <button
                    type="button"
                    onclick="window.print()"
                    class="btn btn-secondary print:hidden">
                    Cetak
                </button>
            </x-slot>
        @endif
    </x-page-header>

    <div class="print:hidden">
        <x-card
            title="Filter Laporan"
            description="Pilih anggota dan periode laporan simpanan.">

            <form method="GET" action="{{ route('reports.savings.index') }}">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                    @if(auth()->user()?->hasRole('SuperAdmin'))
                        <div>
                            <label for="branch_id" class="form-label">Cabang</label>
                            <select
                                id="branch_id"
                                name="branch_id"
                                class="form-select"
                                onchange="this.form.submit()">
                                <option value="">Pilih Cabang</option>
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
                                <option value="">Pilih Anggota</option>
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
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ old('start_date', request('start_date', $startDate->toDateString())) }}"
                            class="form-control">
                    </div>

                    <div>
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ old('end_date', request('end_date', $endDate->toDateString())) }}"
                            class="form-control">
                    </div>
                </div>

                @error('end_date')
                    <p class="form-error mt-2">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                    <button type="submit" class="btn btn-primary">
                        Tampilkan Laporan
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    @if(!$selectedMember && !auth()->user()?->hasRole('Anggota'))
        <div class="mt-6 print:hidden">
            <x-card>
                <div class="empty-state">
                    Pilih anggota terlebih dahulu untuk menampilkan laporan simpanan.
                </div>
            </x-card>
        </div>
    @endif

    @if($selectedMember)
        <div class="mt-6">
            <x-card>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Rekening Koran Simpanan
                        </div>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                            {{ $selectedMember->name }}
                        </h2>

                        <div class="mt-4 info-list">
                            <div class="info-row">
                                <span>No. Anggota</span>
                                <strong>{{ $selectedMember->member_number }}</strong>
                            </div>
                            <div class="info-row">
                                <span>Cabang</span>
                                <strong>
                                    {{ $selectedMember->branch->code ?? '-' }}
                                    -
                                    {{ $selectedMember->branch->name ?? '-' }}
                                </strong>
                            </div>
                            <div class="info-row">
                                <span>Periode</span>
                                <strong>
                                    {{ $startDate->format('d/m/Y') }}
                                    s/d
                                    {{ $endDate->format('d/m/Y') }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5 dark:bg-slate-800/60">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Saldo Awal
                        </div>
                        <div class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">
                            Rp {{ number_format($openingBalance, 0, ',', '.') }}
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Total Debit</div>
                                <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Total Kredit</div>
                                <div class="mt-1 font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($totalCredit, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-700">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Saldo Akhir
                            </div>
                            <div class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                                Rp {{ number_format($closingBalance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="mt-6">
            <x-card
                title="Mutasi Simpanan"
                description="Debit mengurangi saldo, kredit menambah saldo.">

                <div class="hidden md:block">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Jenis Simpanan</th>
                                    <th>Keterangan</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Kredit</th>
                                    <th class="text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $startDate->format('d/m/Y') }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><strong>Saldo Awal</strong></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">
                                        <strong>Rp {{ number_format($openingBalance, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>

                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                                {{ $transaction->trx_no }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $transaction->savingType->code ?? '-' }}
                                            -
                                            {{ $transaction->savingType->name ?? '-' }}
                                        </td>
                                        <td>{{ $transaction->remarks ?: $transaction->mutation_type }}</td>
                                        <td class="text-right">
                                            @if((float) $transaction->debit > 0)
                                                Rp {{ number_format((float) $transaction->debit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if((float) $transaction->credit > 0)
                                                Rp {{ number_format((float) $transaction->credit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <strong>
                                                Rp {{ number_format((float) $transaction->running_balance, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            Tidak ada transaksi simpanan pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total Periode</th>
                                    <th class="text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
                                    <th class="text-right">Rp {{ number_format($totalCredit, 0, ',', '.') }}</th>
                                    <th class="text-right">Rp {{ number_format($closingBalance, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 md:hidden">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-xs text-slate-500 dark:text-slate-400">Saldo Awal</div>
                        <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($openingBalance, 0, ',', '.') }}
                        </div>
                    </div>

                    @foreach($transactions as $transaction)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $transaction->trx_no }}
                                    </div>
                                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                        {{ $transaction->savingType->name ?? '-' }}
                                    </div>
                                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $transaction->transaction_date?->format('d/m/Y') ?? '-' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if((float) $transaction->credit > 0)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Kredit</div>
                                        <div class="font-bold">Rp {{ number_format((float) $transaction->credit, 0, ',', '.') }}</div>
                                    @else
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Debit</div>
                                        <div class="font-bold">Rp {{ number_format((float) $transaction->debit, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-700">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-slate-500 dark:text-slate-400">Saldo</span>
                                    <strong>Rp {{ number_format((float) $transaction->running_balance, 0, ',', '.') }}</strong>
                                </div>
                                @if($transaction->remarks)
                                    <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $transaction->remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif

    <style>
        @media print {
            nav,
            aside,
            header .print\:hidden,
            .print\:hidden {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .shadow,
            [class*="shadow-"] {
                box-shadow: none !important;
            }
        }
    </style>
</x-app-layout>
