<x-app-layout>
    <x-slot name="title">Laba Rugi</x-slot>

    <x-page-header
        title="Laba Rugi"
        description="Laporan pendapatan, beban, dan hasil usaha berdasarkan jurnal akuntansi.">
    </x-page-header>

    <x-card>
        <form
            method="GET"
            action="{{ route('income-statement.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 {{ $isSuperAdmin ? 'md:grid-cols-[220px_180px_180px_auto]' : 'md:grid-cols-[180px_180px_auto]' }} md:items-end">

            @if($isSuperAdmin)
                <div>
                    <label for="branch_id" class="form-label">Cabang</label>
                    <select id="branch_id" name="branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected((string) request('branch_id') === (string) $branch->id)>
                                {{ $branch->code }} - {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
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

            <div>
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

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('income-statement.index') }}" class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Periode</div>
                <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    -
                    {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') }}
                </div>
            </div>

            <div>
                @if($incomeStatement['is_profit'])
                    <span class="status-badge status-active">Laba</span>
                @else
                    <span class="status-badge status-inactive">Rugi</span>
                @endif
            </div>
        </div>

        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Akun</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td colspan="3">
                                <span class="table-primary">PENDAPATAN</span>
                            </td>
                        </tr>

                        @forelse($incomeStatement['revenue_groups'] as $group)
                            @foreach($group->children as $child)
                                <tr>
                                    <td colspan="3">
                                        <span class="table-secondary">
                                            {{ $child->code }} - {{ $child->name }}
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
                                                <span class="table-secondary">Akun nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            {{ $row->amount != 0
                                                ? number_format($row->amount, 2, ',', '.')
                                                : '-' }}
                                        </td>
                                    </tr>
                                @endforeach

                                <tr>
                                    <td colspan="2">
                                        <span class="table-secondary">
                                            Subtotal {{ $child->name }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <span class="table-primary">
                                            {{ number_format($child->total, 2, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">
                                    Tidak ada akun pendapatan.
                                </td>
                            </tr>
                        @endforelse

                        <tr>
                            <td colspan="2">
                                <span class="table-primary">TOTAL PENDAPATAN</span>
                            </td>
                            <td class="text-right">
                                <span class="table-primary">
                                    {{ number_format($incomeStatement['total_revenue'], 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                <span class="table-primary">BEBAN</span>
                            </td>
                        </tr>

                        @forelse($incomeStatement['expense_groups'] as $group)
                            @foreach($group->children as $child)
                                <tr>
                                    <td colspan="3">
                                        <span class="table-secondary">
                                            {{ $child->code }} - {{ $child->name }}
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
                                                <span class="table-secondary">Akun nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            {{ $row->amount != 0
                                                ? number_format($row->amount, 2, ',', '.')
                                                : '-' }}
                                        </td>
                                    </tr>
                                @endforeach

                                <tr>
                                    <td colspan="2">
                                        <span class="table-secondary">
                                            Subtotal {{ $child->name }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <span class="table-primary">
                                            {{ number_format($child->total, 2, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">
                                    Tidak ada akun beban.
                                </td>
                            </tr>
                        @endforelse

                        <tr>
                            <td colspan="2">
                                <span class="table-primary">TOTAL BEBAN</span>
                            </td>
                            <td class="text-right">
                                <span class="table-primary">
                                    {{ number_format($incomeStatement['total_expense'], 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                        <th colspan="2">
                            {{ $incomeStatement['is_profit'] ? 'LABA BERSIH' : 'RUGI BERSIH' }}
                        </th>
                        <th class="text-right">
                            {{ number_format(abs($incomeStatement['net_income']), 2, ',', '.') }}
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-4 md:hidden">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">
                <div class="font-bold text-slate-900 dark:text-white">
                    Pendapatan
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($incomeStatement['revenue_rows'] as $row)
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
                                Rp {{ number_format($row->amount, 2, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada akun pendapatan.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div class="flex justify-between gap-3">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Total Pendapatan
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($incomeStatement['total_revenue'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">
                <div class="font-bold text-slate-900 dark:text-white">
                    Beban
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($incomeStatement['expense_rows'] as $row)
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
                                Rp {{ number_format($row->amount, 2, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada akun beban.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div class="flex justify-between gap-3">
                        <span class="font-bold text-slate-900 dark:text-white">
                            Total Beban
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($incomeStatement['total_expense'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                        dark:border-slate-700 dark:bg-slate-800/60">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-bold text-slate-900 dark:text-white">
                        {{ $incomeStatement['is_profit'] ? 'Laba Bersih' : 'Rugi Bersih' }}
                    </span>
                    <span class="text-lg font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format(abs($incomeStatement['net_income']), 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </x-card>
</x-app-layout>
