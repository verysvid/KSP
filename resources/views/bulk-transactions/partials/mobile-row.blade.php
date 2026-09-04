<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm
            dark:border-slate-700 dark:bg-slate-900">

    {{-- HEADER ANGGOTA --}}
    <div class="flex items-start gap-3 border-b border-slate-200 px-4 py-4
                dark:border-slate-700">

        <div class="pt-1">
            <input
                type="checkbox"
                name="member_ids[]"
                value="{{ $row['member_id'] }}"
                class="member-checkbox member-checkbox-mobile h-5 w-5 rounded
                       border-slate-300 text-indigo-600 focus:ring-indigo-500"
                data-member-id="{{ $row['member_id'] }}"
                @disabled(!$row['selectable'])
                @checked(in_array(
                    (string) $row['member_id'],
                    array_map('strval', old('member_ids', [])),
                    true
                ))
            >
        </div>

        <div class="min-w-0 flex-1">

            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ $row['name'] }}
                    </div>

                    <div class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        {{ $row['member_number'] }}
                    </div>
                </div>

                <div class="shrink-0 text-sm font-semibold text-slate-500
                            dark:text-slate-400">
                    #{{ $row['no'] }}
                </div>
            </div>

            @if($row['processed_batch'])
                <div class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold
                            text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                    Sudah diproses: {{ $row['processed_batch'] }}
                </div>
            @elseif($row['blocked_reason'])
                <div class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold
                            text-red-700 dark:bg-red-950/40 dark:text-red-300">
                    {{ $row['blocked_reason'] }}
                </div>
            @endif

        </div>
    </div>


    {{-- TOTAL UTAMA --}}
    <div class="bg-indigo-50 px-4 py-3 dark:bg-indigo-950/30">
        <div class="flex items-center justify-between gap-4">
            <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                Jumlah Semua Potongan
            </span>

            <span class="text-base font-bold text-indigo-700 dark:text-indigo-300">
                Rp {{ number_format((float) ($row['all_total'] ?? 0), 0, ',', '.') }}
            </span>
        </div>
    </div>


    {{-- DETAIL --}}
    <details class="group">
        <summary class="flex cursor-pointer list-none items-center justify-between
                        px-4 py-3 text-sm font-semibold text-slate-700
                        dark:text-slate-300">
            <span>Lihat Rincian Transaksi</span>

            <svg class="h-5 w-5 transition-transform group-open:rotate-180"
                 viewBox="0 0 20 20"
                 fill="currentColor"
                 aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75
                         0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08
                         0l-4.25-4.51a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
            </svg>
        </summary>


        <div class="border-t border-slate-200 px-4 py-4
                    dark:border-slate-700">

            {{-- SIMPANAN --}}
            <div>
                <div class="mb-3 text-xs font-bold uppercase tracking-wide
                            text-slate-500 dark:text-slate-400">
                    Simpanan
                </div>

                <div class="space-y-2">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Pokok
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['saving_principal']
                                ? 'Rp '.number_format($row['saving_principal'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Wajib
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['saving_mandatory']
                                ? 'Rp '.number_format($row['saving_mandatory'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Manasuka
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['saving_voluntary']
                                ? 'Rp '.number_format($row['saving_voluntary'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                </div>
            </div>


            {{-- PINJAMAN UANG --}}
            <div class="mt-5 border-t border-slate-100 pt-4
                        dark:border-slate-800">

                <div class="mb-3 text-xs font-bold uppercase tracking-wide
                            text-slate-500 dark:text-slate-400">
                    Pinjaman Uang
                </div>

                <div class="space-y-2">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Pinjaman Uang
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['money_opening']
                                ? 'Rp '.number_format($row['money_opening'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Potongan
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['money_principal']
                                ? 'Rp '.number_format($row['money_principal'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Angsuran Ke
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['money_installment_no'] ?: '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Jasa
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['money_interest']
                                ? 'Rp '.number_format($row['money_interest'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Sisa
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['money_ending']
                                ? 'Rp '.number_format($row['money_ending'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4
                                border-t border-slate-100 pt-2
                                dark:border-slate-800">
                        <span class="text-sm font-semibold text-slate-700
                                     dark:text-slate-300">
                            Jumlah Pot. Uang
                        </span>

                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $row['money_total']
                                ? 'Rp '.number_format($row['money_total'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                </div>
            </div>


            {{-- PINJAMAN BARANG --}}
            <div class="mt-5 border-t border-slate-100 pt-4
                        dark:border-slate-800">

                <div class="mb-3 text-xs font-bold uppercase tracking-wide
                            text-slate-500 dark:text-slate-400">
                    Pinjaman Barang
                </div>

                <div class="space-y-2">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Barang
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['goods_opening']
                                ? 'Rp '.number_format($row['goods_opening'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Potongan
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['goods_principal']
                                ? 'Rp '.number_format($row['goods_principal'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Angsuran Ke
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['goods_installment_no'] ?: '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Jasa
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['goods_interest']
                                ? 'Rp '.number_format($row['goods_interest'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Sisa
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $row['goods_ending']
                                ? 'Rp '.number_format($row['goods_ending'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4
                                border-t border-slate-100 pt-2
                                dark:border-slate-800">
                        <span class="text-sm font-semibold text-slate-700
                                     dark:text-slate-300">
                            Jumlah Pot. Barang
                        </span>

                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $row['goods_total']
                                ? 'Rp '.number_format($row['goods_total'], 0, ',', '.')
                                : '-' }}
                        </span>
                    </div>

                </div>
            </div>


            {{-- RINGKASAN --}}
            <div class="mt-5 rounded-lg bg-slate-50 p-3
                        dark:bg-slate-800/70">

                <div class="space-y-2">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">
                            Jumlah Uang + Barang
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            Rp {{ number_format((float) ($row['loan_total'] ?? 0), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4
                                border-t border-slate-200 pt-2
                                dark:border-slate-700">

                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                            Jumlah Semua Potongan
                        </span>

                        <span class="text-base font-bold text-indigo-600
                                     dark:text-indigo-400">
                            Rp {{ number_format((float) ($row['all_total'] ?? 0), 0, ',', '.') }}
                        </span>

                    </div>

                </div>
            </div>

        </div>
    </details>
</div>