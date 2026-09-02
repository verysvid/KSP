<x-app-layout>
    <x-slot name="title">Daftar Potongan Anggota</x-slot>

    <x-page-header
        title="Daftar Potongan Anggota"
        description="Laporan bulanan simpanan dan potongan pinjaman anggota per cabang.">
        @if($branch && $report)
            <x-slot name="actions">
                <a href="{{ route('reports.member-deductions.excel', ['month' => $month, 'year' => $year, 'branch_id' => $branch->id]) }}"
                   class="btn btn-secondary">
                    Ekspor Excel
                </a>
                <a href="{{ route('reports.member-deductions.pdf', ['month' => $month, 'year' => $year, 'branch_id' => $branch->id]) }}"
                   class="btn btn-primary">
                    Ekspor PDF
                </a>
            </x-slot>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET"
              action="{{ route('reports.member-deductions.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[180px_180px_1fr_auto] md:items-end">
            <div>
                <label for="month" class="form-label">Bulan</label>
                <select id="month" name="month" class="form-select" required>
                    @foreach(range(1, 12) as $monthOption)
                        <option value="{{ $monthOption }}" @selected($month === $monthOption)>
                            {{ \Carbon\Carbon::create(null, $monthOption, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="year" class="form-label">Tahun</label>
                <select id="year" name="year" class="form-select" required>
                    @foreach(range(now()->year + 1, now()->year - 10) as $yearOption)
                        <option value="{{ $yearOption }}" @selected($year === $yearOption)>
                            {{ $yearOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="branch_id" class="form-label">Cabang</label>
                @if($isSuperAdmin)
                    <select id="branch_id" name="branch_id" class="form-select" required>
                        <option value="">Pilih Cabang</option>
                        @foreach($branches as $branchOption)
                            <option value="{{ $branchOption->id }}"
                                    @selected((string) request('branch_id') === (string) $branchOption->id)>
                                {{ $branchOption->code }} - {{ $branchOption->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text"
                           id="branch_id"
                           class="form-control"
                           value="{{ $currentBranch?->code }} - {{ $currentBranch?->name }}"
                           disabled>
                @endif
                @error('branch_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('reports.member-deductions.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        @if(!$branch)
            <div class="empty-state">
                Pilih periode dan cabang untuk menampilkan laporan.
            </div>
        @else
            <div class="mb-5 border-b border-slate-200 pb-4 dark:border-slate-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    DAFTAR POTONGAN ANGGOTA
                </h2>
                <div class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    {{ $branch->code }} - {{ $branch->name }}
                </div>
                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Periode: {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                    · {{ number_format($report['memberCount'], 0, ',', '.') }} anggota
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table min-w-[2100px]">
                    @include('reports.member-deductions.partials.table-head')
                    <tbody>
                    @forelse($report['groups'] as $group)
                        <tr class="bg-indigo-50 dark:bg-indigo-950/40">
                            <td colspan="19" class="font-bold text-indigo-700 dark:text-indigo-300">
                                GROUP: {{ $group['name'] }}
                            </td>
                        </tr>
                        @foreach($group['rows'] as $row)
                            @include('reports.member-deductions.partials.web-row', ['row' => $row])
                        @endforeach
                        @include('reports.member-deductions.partials.web-total-row', [
                            'label' => 'SUBTOTAL ' . $group['name'],
                            'totals' => $group['subtotal'],
                            'class' => 'bg-slate-100 font-semibold dark:bg-slate-800',
                        ])
                    @empty
                        <tr><td colspan="19" class="empty-state">Tidak ada anggota untuk periode dan cabang ini.</td></tr>
                    @endforelse
                    @if($report['memberCount'] > 0)
                        @include('reports.member-deductions.partials.web-total-row', [
                            'label' => 'JUMLAH KESELURUHAN',
                            'totals' => $report['totals'],
                            'class' => 'bg-indigo-100 font-bold dark:bg-indigo-950/60',
                        ])
                    @endif
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-app-layout>
