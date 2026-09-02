<x-app-layout>
    <x-slot name="title">Neraca Saldo</x-slot>

    <x-page-header
        title="Neraca Saldo"
        description="Trial Balance berdasarkan jurnal akuntansi dan Chart of Accounts.">
    </x-page-header>

    <x-card>
        <form
            method="GET"
            action="{{ route('trial-balance.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 {{ $isSuperAdmin ? 'md:grid-cols-[220px_180px_180px_auto]' : 'md:grid-cols-[180px_180px_auto]' }} md:items-end">

            @if($isSuperAdmin)
                <div>
                    <label for="branch_id" class="form-label">
                        Cabang
                    </label>

                    <select
                        id="branch_id"
                        name="branch_id"
                        class="form-select">
                        <option value="">Semua Cabang</option>

                        @foreach($branches as $branch)
                            <option
                                value="{{ $branch->id }}"
                                @selected(
                                    (string) request('branch_id')
                                    === (string) $branch->id
                                )>
                                {{ $branch->code }} - {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="date_from" class="form-label">
                    Tanggal Dari
                </label>

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

            <div>
                <label for="date_to" class="form-label">
                    Tanggal Sampai
                </label>

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

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="btn btn-primary">
                    Tampilkan
                </button>

                <a
                    href="{{ route('trial-balance.index') }}"
                    class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Periode
                </div>

                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    -
                    {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') }}
                </div>
            </div>

            <div>
                @if($trialBalance['is_balanced'])
                    <span class="status-badge status-active">
                        Balance
                    </span>
                @else
                    <span class="status-badge status-inactive">
                        Selisih Rp {{ number_format(abs($trialBalance['difference']), 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Nama Akun</th>
                        <th colspan="2" class="text-center">
                            Saldo Awal
                        </th>
                        <th colspan="2" class="text-center">
                            Mutasi Periode
                        </th>
                        <th colspan="2" class="text-center">
                            Saldo Akhir
                        </th>
                    </tr>
                    <tr>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($trialBalance['groups'] as $group)
                        <tr>
                            <td colspan="8">
                                <span class="table-primary">
                                    {{ $group->code }} - {{ $group->name }}
                                </span>
                            </td>
                        </tr>

                        @foreach($group->children as $child)
                            @if(
                                $child->code !== $group->code
                                || $child->name !== $group->name
                            )
                                <tr>
                                    <td colspan="8">
                                        <span class="table-secondary">
                                            {{ $child->code }} - {{ $child->name }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @foreach($child->rows as $row)
                                <tr>
                                    <td>
                                        <span class="table-primary">
                                            {{ $row->account->code }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="table-primary">
                                            {{ $row->account->name }}
                                        </span>

                                        @if(!$row->account->is_active)
                                            <span class="table-secondary">
                                                Akun nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        {{ $row->opening_debit > 0
                                            ? number_format($row->opening_debit, 2, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $row->opening_credit > 0
                                            ? number_format($row->opening_credit, 2, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $row->period_debit > 0
                                            ? number_format($row->period_debit, 2, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $row->period_credit > 0
                                            ? number_format($row->period_credit, 2, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $row->closing_debit > 0
                                            ? number_format($row->closing_debit, 2, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $row->closing_credit > 0
                                            ? number_format($row->closing_credit, 2, ',', '.')
                                            : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                Tidak ada akun posting.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot>
                    <tr>
                        <th colspan="2">
                            TOTAL
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['opening_debit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['opening_credit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['period_debit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['period_credit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['closing_debit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>

                        <th class="text-right">
                            {{ number_format(
                                $trialBalance['totals']['closing_credit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse($trialBalance['rows'] as $row)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $row->account->code }}
                            </div>

                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                {{ $row->account->name }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $row->parent_code && $row->parent_name
                                    ? $row->parent_code . ' - ' . $row->parent_name
                                    : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Saldo Awal Debit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->opening_debit, 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Saldo Awal Kredit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->opening_credit, 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Mutasi Debit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->period_debit, 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Mutasi Kredit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->period_credit, 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Saldo Akhir Debit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->closing_debit, 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                Saldo Akhir Kredit
                            </div>
                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($row->closing_credit, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada akun posting.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-layout>
