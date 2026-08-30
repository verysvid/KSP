<x-app-layout>
    <x-slot name="title">Detail Jurnal</x-slot>

    <x-page-header
        title="Detail Jurnal"
        description="{{ $journalEntry->journal_no }}">

        <x-slot name="actions">
            <a href="{{ route('journal-entries.index') }}"
               class="btn btn-secondary">
                Kembali
            </a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center
                            rounded-2xl bg-indigo-100 text-xl font-extrabold
                            text-indigo-700
                            dark:bg-indigo-500/15 dark:text-indigo-300">
                    JR
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $journalEntry->journal_no }}
                </h2>

                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ $journalEntry->journal_date?->format('d/m/Y') ?? '-' }}
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 px-4 py-4 dark:bg-slate-800/60">
                    <div class="text-xs font-medium uppercase tracking-wide
                                text-slate-500 dark:text-slate-400">
                        Total Jurnal
                    </div>

                    <div class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $journalEntry->lines->sum('debit'), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Informasi Jurnal"
            description="Informasi header jurnal akuntansi.">

            <div class="info-list">
                <div class="info-row">
                    <span>No. Jurnal</span>
                    <strong>{{ $journalEntry->journal_no }}</strong>
                </div>

                <div class="info-row">
                    <span>Tanggal</span>
                    <strong>{{ $journalEntry->journal_date?->format('d/m/Y') ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Cabang</span>
                    <strong>
                        {{ $journalEntry->branch->code ?? '-' }}
                        -
                        {{ $journalEntry->branch->name ?? '-' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Keterangan</span>
                    <strong class="max-w-md">{{ $journalEntry->description }}</strong>
                </div>

                <div class="info-row">
                    <span>Jenis Referensi</span>
                    <strong>{{ class_basename($journalEntry->reference_type ?: '-') }}</strong>
                </div>

                <div class="info-row">
                    <span>ID Referensi</span>
                    <strong>{{ $journalEntry->reference_id ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Dibuat Oleh</span>
                    <strong>{{ $journalEntry->createdBy->name ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Dibuat Pada</span>
                    <strong>{{ $journalEntry->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card
            title="Detail Debit / Kredit"
            description="Rincian akun yang membentuk jurnal.">

            <div class="hidden md:block">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Keterangan</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Kredit</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($journalEntry->lines as $line)
                            <tr>
                                <td>
                                    <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                        {{ $line->account->code ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="table-primary">
                                        {{ $line->account->name ?? '-' }}
                                    </span>
                                </td>

                                <td>{{ $line->description ?: '-' }}</td>

                                <td class="text-right">
                                    @if((float) $line->debit > 0)
                                        Rp {{ number_format((float) $line->debit, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if((float) $line->credit > 0)
                                        Rp {{ number_format((float) $line->credit, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">TOTAL</th>
                            <th class="text-right">
                                Rp {{ number_format((float) $journalEntry->lines->sum('debit'), 0, ',', '.') }}
                            </th>
                            <th class="text-right">
                                Rp {{ number_format((float) $journalEntry->lines->sum('credit'), 0, ',', '.') }}
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                @foreach($journalEntry->lines as $line)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                                dark:border-slate-700 dark:bg-slate-800/60">

                        <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $line->account->code ?? '-' }}
                        </div>

                        <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                            {{ $line->account->name ?? '-' }}
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Debit</div>
                                <div class="mt-1 font-semibold">
                                    Rp {{ number_format((float) $line->debit, 0, ',', '.') }}
                                </div>
                            </div>

                            <div>
                                <div class="text-slate-500 dark:text-slate-400">Kredit</div>
                                <div class="mt-1 font-semibold">
                                    Rp {{ number_format((float) $line->credit, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</x-app-layout>
