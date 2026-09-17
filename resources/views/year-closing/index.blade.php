<x-app-layout>
    <x-slot name="title">Tutup Buku</x-slot>

    <x-page-header
        title="Tutup Buku Akhir Tahun"
        description="Review laporan keuangan, simpan snapshot resmi, dan kunci transaksi periode yang sudah ditutup.">
    </x-page-header>

    <x-card title="Preview Tahun Buku" description="Pilih tahun dan cabang. Sistem akan memeriksa kelayakan sebelum proses tutup buku.">
        <form method="GET" action="{{ route('year-closing.preview') }}">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">
                @if($isSuperAdmin)
                    <div>
                        <label class="form-label" for="branch_id">Cabang</label>
                        <select id="branch_id" name="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                                    {{ $branch->code }} - {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="form-label" for="year">Tahun Buku</label>
                    <input
                        id="year"
                        name="year"
                        type="number"
                        min="2000"
                        max="2100"
                        value="{{ request('year', now()->subYear()->year) }}"
                        class="form-control"
                        required>
                </div>

                <div>
                    <button class="btn btn-primary" type="submit">Preview Tutup Buku</button>
                </div>
            </div>
        </form>
    </x-card>

    <div class="mt-6">
        <x-card title="Riwayat Tutup Buku" description="CLOSED mengunci periode. REOPENED berarti periode dibuka kembali untuk koreksi.">
            <form method="GET" action="{{ route('year-closing.index') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4 md:items-end">
                @if($isSuperAdmin)
                    <div>
                        <label class="form-label">Cabang</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                                    {{ $branch->code }} - {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="form-label">Tahun</label>
                    <input name="year" type="number" min="2000" max="2100" value="{{ request('year') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="CLOSED" @selected(request('status') === 'CLOSED')>CLOSED</option>
                        <option value="REOPENED" @selected(request('status') === 'REOPENED')>REOPENED</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('year-closing.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <div class="hidden md:block table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th class="text-right">Pendapatan</th>
                        <th class="text-right">Beban</th>
                        <th class="text-right">SHU</th>
                        <th>Closed At</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($closings as $closing)
                        <tr>
                            <td class="font-bold">{{ $closing->year }}</td>
                            <td>{{ $closing->branch?->code }} - {{ $closing->branch?->name }}</td>
                            <td>
                                <span class="status-badge {{ $closing->status === 'CLOSED' ? 'status-active' : 'status-inactive' }}">
                                    {{ $closing->status }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format((float) $closing->total_revenue, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format((float) $closing->total_expense, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format((float) $closing->net_shu, 0, ',', '.') }}</td>
                            <td>{{ $closing->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-right"><a class="btn btn-secondary" href="{{ route('year-closing.show', $closing) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">Belum ada tahun buku yang ditutup.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse($closings as $closing)
                    <a href="{{ route('year-closing.show', $closing) }}" class="block rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-lg font-extrabold">Tahun Buku {{ $closing->year }}</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $closing->branch?->code }} - {{ $closing->branch?->name }}</div>
                            </div>
                            <span class="status-badge {{ $closing->status === 'CLOSED' ? 'status-active' : 'status-inactive' }}">{{ $closing->status }}</span>
                        </div>
                        <div class="mt-4 flex justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                            <span class="text-sm text-slate-500 dark:text-slate-400">SHU Snapshot</span>
                            <strong>Rp {{ number_format((float) $closing->net_shu, 0, ',', '.') }}</strong>
                        </div>
                    </a>
                @empty
                    <div class="empty-state">Belum ada tahun buku yang ditutup.</div>
                @endforelse
            </div>

            <div class="mt-4">{{ $closings->links() }}</div>
        </x-card>
    </div>
</x-app-layout>
