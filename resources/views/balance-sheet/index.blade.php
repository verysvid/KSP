<x-app-layout>
    <x-slot name="title">Neraca</x-slot>

    <x-page-header
        title="Neraca"
        description="Balance Sheet posisi Aset, Liabilitas, dan Ekuitas pada tanggal laporan.">
    </x-page-header>

    <x-card>
        <form
            method="GET"
            action="{{ route('balance-sheet.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 {{ $isSuperAdmin ? 'md:grid-cols-[240px_180px_auto]' : 'md:grid-cols-[180px_auto]' }} md:items-end">

            @if($isSuperAdmin)
                <div>
                    <label
                        for="branch_id"
                        class="form-label">
                        Cabang
                    </label>

                    <select
                        id="branch_id"
                        name="branch_id"
                        class="form-select">

                        <option value="">
                            Semua Cabang
                        </option>

                        @foreach($branches as $branch)
                            <option
                                value="{{ $branch->id }}"
                                @selected(
                                    (string) request('branch_id')
                                    === (string) $branch->id
                                )>
                                {{ $branch->code }}
                                -
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label
                    for="as_of_date"
                    class="form-label">
                    Tanggal Neraca
                </label>

                <input
                    id="as_of_date"
                    name="as_of_date"
                    type="date"
                    value="{{ old('as_of_date', $asOfDate) }}"
                    class="form-control"
                    required>

                @error('as_of_date')
                    <p class="form-error">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="btn btn-primary">
                    Tampilkan
                </button>

                <a
                    href="{{ route('balance-sheet.index') }}"
                    class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Posisi per tanggal
                </div>

                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ \Illuminate\Support\Carbon::parse($asOfDate)->format('d/m/Y') }}
                </div>
            </div>

            <div>
                @if($balanceSheet['is_balanced'])
                    <span class="status-badge status-active">
                        Balance
                    </span>
                @else
                    <span class="status-badge status-inactive">
                        Selisih Rp
                        {{ number_format(
                            abs($balanceSheet['difference']),
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="hidden md:block">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    ASET
                                </th>
                                <th class="text-right">
                                    Jumlah
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse($balanceSheet['asset_groups'] as $group)
                                @foreach($group->children as $child)
                                    <tr>
                                        <td colspan="3">
                                            <span class="table-secondary">
                                                {{ $child->code }}
                                                -
                                                {{ $child->name }}
                                            </span>
                                        </td>
                                    </tr>

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
                                                {{ $row->amount != 0
                                                    ? number_format(
                                                        $row->amount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="2">
                                            <span class="table-secondary">
                                                Subtotal
                                                {{ $child->name }}
                                            </span>
                                        </td>

                                        <td class="text-right">
                                            <span class="table-primary">
                                                {{ number_format(
                                                    $child->total,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td
                                        colspan="3"
                                        class="empty-state">
                                        Tidak ada akun aset.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>

                            <tfoot>
                            <tr>
                                <th colspan="2">
                                    TOTAL ASET
                                </th>

                                <th class="text-right">
                                    {{ number_format(
                                        $balanceSheet['total_assets'],
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

                <div class="space-y-6">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    LIABILITAS
                                </th>
                                <th class="text-right">
                                    Jumlah
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse($balanceSheet['liability_groups'] as $group)
                                @foreach($group->children as $child)
                                    <tr>
                                        <td colspan="3">
                                            <span class="table-secondary">
                                                {{ $child->code }}
                                                -
                                                {{ $child->name }}
                                            </span>
                                        </td>
                                    </tr>

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
                                                {{ $row->amount != 0
                                                    ? number_format(
                                                        $row->amount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="2">
                                            <span class="table-secondary">
                                                Subtotal
                                                {{ $child->name }}
                                            </span>
                                        </td>

                                        <td class="text-right">
                                            <span class="table-primary">
                                                {{ number_format(
                                                    $child->total,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td
                                        colspan="3"
                                        class="empty-state">
                                        Tidak ada akun liabilitas.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>

                            <tfoot>
                            <tr>
                                <th colspan="2">
                                    TOTAL LIABILITAS
                                </th>

                                <th class="text-right">
                                    {{ number_format(
                                        $balanceSheet['total_liabilities'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    EKUITAS
                                </th>
                                <th class="text-right">
                                    Jumlah
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse($balanceSheet['equity_groups'] as $group)
                                @foreach($group->children as $child)
                                    <tr>
                                        <td colspan="3">
                                            <span class="table-secondary">
                                                {{ $child->code }}
                                                -
                                                {{ $child->name }}
                                            </span>
                                        </td>
                                    </tr>

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
                                                {{ $row->amount != 0
                                                    ? number_format(
                                                        $row->amount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @empty
                                <tr>
                                    <td
                                        colspan="3"
                                        class="empty-state">
                                        Belum ada akun ekuitas posting.
                                    </td>
                                </tr>
                            @endforelse

                            <tr>
                                <td>
                                    <span class="table-primary">
                                        -
                                    </span>
                                </td>

                                <td>
                                    <span class="table-primary">
                                        SHU / Laba (Rugi) Tahun Berjalan
                                    </span>

                                    <span class="table-secondary">
                                        Dihitung otomatis dari Pendapatan
                                        dan Beban tahun berjalan.
                                    </span>
                                </td>

                                <td class="text-right">
                                    <span class="table-primary">
                                        {{ number_format(
                                            $balanceSheet['current_year_profit'],
                                            2,
                                            ',',
                                            '.'
                                        ) }}
                                    </span>
                                </td>
                            </tr>
                            </tbody>

                            <tfoot>
                            <tr>
                                <th colspan="2">
                                    TOTAL EKUITAS
                                </th>

                                <th class="text-right">
                                    {{ number_format(
                                        $balanceSheet['total_equity'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <tfoot>
                            <tr>
                                <th colspan="2">
                                    TOTAL LIABILITAS + EKUITAS
                                </th>

                                <th class="text-right">
                                    {{ number_format(
                                        $balanceSheet['total_liabilities_and_equity'],
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
            </div>
        </div>

        <div class="space-y-4 md:hidden">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">

                <div class="font-bold text-slate-900 dark:text-white">
                    Aset
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($balanceSheet['asset_rows'] as $row)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $row->account->code }}
                                </div>

                                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $row->account->name }}
                                </div>
                            </div>

                            <div class="text-right font-semibold text-slate-900 dark:text-white">
                                Rp
                                {{ number_format(
                                    $row->amount,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada akun aset.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div class="flex justify-between gap-3">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Total Aset
                        </span>

                        <span class="font-bold text-slate-900 dark:text-white">
                            Rp
                            {{ number_format(
                                $balanceSheet['total_assets'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">

                <div class="font-bold text-slate-900 dark:text-white">
                    Liabilitas
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($balanceSheet['liability_rows'] as $row)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $row->account->code }}
                                </div>

                                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $row->account->name }}
                                </div>
                            </div>

                            <div class="text-right font-semibold text-slate-900 dark:text-white">
                                Rp
                                {{ number_format(
                                    $row->amount,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada akun liabilitas.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div class="flex justify-between gap-3">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Total Liabilitas
                        </span>

                        <span class="font-bold text-slate-900 dark:text-white">
                            Rp
                            {{ number_format(
                                $balanceSheet['total_liabilities'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">

                <div class="font-bold text-slate-900 dark:text-white">
                    Ekuitas
                </div>

                <div class="mt-3 space-y-3">
                    @foreach($balanceSheet['equity_rows'] as $row)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $row->account->code }}
                                </div>

                                <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $row->account->name }}
                                </div>
                            </div>

                            <div class="text-right font-semibold text-slate-900 dark:text-white">
                                Rp
                                {{ number_format(
                                    $row->amount,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                SHU / Laba (Rugi) Tahun Berjalan
                            </div>

                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Otomatis dari Pendapatan dan Beban.
                            </div>
                        </div>

                        <div class="text-right font-semibold text-slate-900 dark:text-white">
                            Rp
                            {{ number_format(
                                $balanceSheet['current_year_profit'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div class="flex justify-between gap-3">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Total Ekuitas
                        </span>

                        <span class="font-bold text-slate-900 dark:text-white">
                            Rp
                            {{ number_format(
                                $balanceSheet['total_equity'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">

                <div class="flex justify-between gap-3">
                    <span class="font-bold text-slate-900 dark:text-white">
                        Liabilitas + Ekuitas
                    </span>

                    <span class="font-bold text-slate-900 dark:text-white">
                        Rp
                        {{ number_format(
                            $balanceSheet['total_liabilities_and_equity'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>
                </div>
            </div>
        </div>
    </x-card>
</x-app-layout>
