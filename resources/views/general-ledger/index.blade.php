<x-app-layout>
    <x-slot name="title">Buku Besar</x-slot>

    <x-page-header
        title="Buku Besar"
        description="General Ledger berdasarkan jurnal akuntansi yang telah diposting." />

    <x-card>
        <form
            method="GET"
            action="{{ route('general-ledger.index') }}"
            class="mb-6 flex flex-col gap-3 md:flex-row md:items-end">

            <div class="min-w-0 flex-1">
                <label for="account_id" class="form-label">Akun</label>
                <select id="account_id" name="account_id" class="form-select">
                    <option value="">Pilih Akun</option>
                    @foreach($accounts as $account)
                        <option
                            value="{{ $account->id }}"
                            @selected((string) request('account_id') === (string) $account->id)>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($isSuperAdmin)
                <div class="w-full md:w-56">
                    <label for="branch_id" class="form-label">Cabang</label>
                    <select id="branch_id" name="branch_id" class="form-select">
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

            <div class="w-full md:w-48">
                <label for="date_from" class="form-label">Tanggal Dari</label>
                <input
                    id="date_from"
                    name="date_from"
                    type="date"
                    value="{{ old('date_from', $dateFrom) }}"
                    class="form-control"
                    required>
                @error('date_from')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full md:w-48">
                <label for="date_to" class="form-label">Tanggal Sampai</label>
                <input
                    id="date_to"
                    name="date_to"
                    type="date"
                    value="{{ old('date_to', $dateTo) }}"
                    class="form-control"
                    required>
                @error('date_to')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex shrink-0 gap-2">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('general-ledger.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        @if(!$selectedAccount)
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                <div class="text-base font-semibold text-slate-900 dark:text-white">
                    Pilih akun untuk menampilkan Buku Besar.
                </div>
                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Tentukan akun dan periode, kemudian klik Tampilkan.
                </div>
            </div>
        @else
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">Akun</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $selectedAccount->type }} · Normal Balance: {{ $selectedAccount->normal_balance }}
                    </div>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Periode:
                    <strong class="text-slate-700 dark:text-slate-200">
                        {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }}
                        -
                        {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') }}
                    </strong>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Saldo Awal</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($ledger['opening_balance'], 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Total Debit</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($ledger['period_debit'], 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Total Kredit</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($ledger['period_credit'], 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Saldo Akhir</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($ledger['closing_balance'], 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="hidden overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/70">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">No. Jurnal</th>
                                @if($isSuperAdmin && !$branchId)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Cabang</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Keterangan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Kredit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            <tr class="bg-slate-50/70 dark:bg-slate-800/30">
                                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 4 : 3 }}"
                                    class="px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Saldo Awal
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-slate-500 dark:text-slate-400">-</td>
                                <td class="px-4 py-3 text-right text-sm text-slate-500 dark:text-slate-400">-</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($ledger['opening_balance'], 2, ',', '.') }}
                                </td>
                            </tr>

                            @forelse($ledger['rows'] as $row)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                        {{ \Illuminate\Support\Carbon::parse($row->journal_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        @if(Route::has('journals.show'))
                                            <a
                                                href="{{ route('journals.show', $row->journal_entry_id) }}"
                                                class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                                                {{ $row->journal_no }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900 dark:text-white">
                                                {{ $row->journal_no }}
                                            </span>
                                        @endif
                                    </td>
                                    @if($isSuperAdmin && !$branchId)
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                            {{ $row->branch_name ?? '-' }}
                                        </td>
                                    @endif
                                    <td class="min-w-64 px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                        {{ $row->line_description ?: $row->journal_description }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700 dark:text-slate-300">
                                        {{ $row->debit > 0 ? 'Rp '.number_format($row->debit, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700 dark:text-slate-300">
                                        {{ $row->credit > 0 ? 'Rp '.number_format($row->credit, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-white">
                                        Rp {{ number_format($row->balance, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($isSuperAdmin && !$branchId) ? 7 : 6 }}"
                                        class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Tidak ada transaksi jurnal pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot class="bg-slate-50 dark:bg-slate-800/70">
                            <tr>
                                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 4 : 3 }}"
                                    class="px-4 py-3 text-sm font-bold text-slate-900 dark:text-white">
                                    TOTAL PERIODE
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($ledger['period_debit'], 2, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($ledger['period_credit'], 2, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($ledger['closing_balance'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/40">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Saldo Awal</div>
                    <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($ledger['opening_balance'], 2, ',', '.') }}
                    </div>
                </div>

                @forelse($ledger['rows'] as $row)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                @if(Route::has('journals.show'))
                                    <a
                                        href="{{ route('journals.show', $row->journal_entry_id) }}"
                                        class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                                        {{ $row->journal_no }}
                                    </a>
                                @else
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ $row->journal_no }}
                                    </div>
                                @endif
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ \Illuminate\Support\Carbon::parse($row->journal_date)->format('d/m/Y') }}
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-xs text-slate-500 dark:text-slate-400">Saldo</div>
                                <div class="font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($row->balance, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        @if($isSuperAdmin && !$branchId)
                            <div class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                                Cabang: {{ $row->branch_name ?? '-' }}
                            </div>
                        @endif

                        <div class="mt-3 text-sm text-slate-700 dark:text-slate-300">
                            {{ $row->line_description ?: $row->journal_description }}
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Debit</div>
                                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                    {{ $row->debit > 0 ? 'Rp '.number_format($row->debit, 2, ',', '.') : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Kredit</div>
                                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                    {{ $row->credit > 0 ? 'Rp '.number_format($row->credit, 2, ',', '.') : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Tidak ada transaksi jurnal pada periode ini.
                    </div>
                @endforelse
            </div>
        @endif
    </x-card>
</x-app-layout>
