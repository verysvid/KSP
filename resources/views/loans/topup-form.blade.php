<x-app-layout>
    <x-slot name="title">{{ $pageTitle }}</x-slot>

    <x-page-header
        title="{{ $pageTitle }}"
        description="TopUp pinjaman aktif {{ $sourceLoan->loan_no }}." />

    <div class="mx-auto max-w-5xl space-y-6">
        @if($errors->has('topup'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errors->first('topup') }}
            </div>
        @endif

        <x-card
            title="Informasi Pinjaman Berjalan"
            description="Data pinjaman sumber yang akan di-TopUp.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="form-label">Nomor Pinjaman</label>
                    <input type="text" value="{{ $snapshot['loan_no'] }}" class="form-control" disabled>
                </div>
                <div>
                    <label class="form-label">Jumlah Pinjaman</label>
                    <input type="text" value="Rp {{ number_format($snapshot['principal_amount'], 0, ',', '.') }}" class="form-control" disabled>
                </div>
                <div>
                    <label class="form-label">Tanggal Terakhir Jatuh Tempo</label>
                    <input type="text" value="{{ $snapshot['last_due_date']?->format('d/m/Y') ?? '-' }}" class="form-control" disabled>
                </div>
                <div>
                    <label class="form-label">Total Tenor</label>
                    <input type="text" value="{{ $snapshot['tenor_months'] }} bulan" class="form-control" disabled>
                </div>
                <div>
                    <label class="form-label">Sisa Pokok</label>
                    <input type="text" value="Rp {{ number_format($snapshot['outstanding_principal'], 0, ',', '.') }}" class="form-control" disabled>
                </div>
                <div>
                    <label class="form-label">Sisa Tenor</label>
                    <input type="text" value="{{ $snapshot['remaining_tenor'] }} bulan" class="form-control" disabled>
                </div>
            </div>
        </x-card>

        <x-card
            title="Informasi Pengajuan TopUp"
            description="Cabang, anggota, jenis pinjaman, metode bunga dan tanggal angsuran mengikuti pinjaman lama. Hanya Nominal TopUp dan Tenor yang dapat diubah.">

            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($formMethod !== 'POST')
                    @method($formMethod)
                @endif

                @php
                    $initialTopUp = old('topup_amount', $topUpLoan?->topup_amount ?? null);
                    $initialTenor = old('tenor_months', $topUpLoan?->tenor_months ?? $sourceLoan->tenor_months);
                    $oldOutstanding = (float) $snapshot['outstanding_principal'];
                @endphp

                <div
                    x-data="topUpLoanForm({
                        initialTopUp: @js($initialTopUp),
                        outstanding: @js($oldOutstanding)
                    })"
                    class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="form-label">Cabang</label>
                        <input type="text" value="{{ ($sourceLoan->branch->code ?? '-') . ' - ' . ($sourceLoan->branch->name ?? '-') }}" class="form-control" disabled>
                    </div>

                    <div>
                        <label class="form-label">Tanggal Pengajuan</label>
                        <input type="text" value="{{ $topUpLoan?->application_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}" class="form-control" disabled>
                    </div>

                    <div>
                        <label class="form-label">Anggota</label>
                        <input type="text" value="{{ $sourceLoan->member->name ?? '-' }}" class="form-control" disabled>
                    </div>

                    <div>
                        <label class="form-label">Jenis Pinjaman</label>
                        <input type="text" value="{{ ($sourceLoan->loanType->code ?? '-') . ' - ' . ($sourceLoan->loanType->name ?? '-') }}" class="form-control" disabled>
                    </div>

                    <div>
                        <label class="form-label">Metode & Bunga</label>
                        <input
                            type="text"
                            value="{{ $sourceLoan->interest_type }} · {{ str_replace('.', ',', rtrim(rtrim(number_format((float) $sourceLoan->interest_rate, 4, '.', ''), '0'), '.')) }}%"
                            class="form-control"
                            disabled>
                    </div>

                    <div>
                        <label class="form-label">Tanggal Jatuh Tempo Angsuran</label>
                        <input type="text" value="Tanggal 20" class="form-control" disabled>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Khusus TopUp, tanggal jatuh tempo otomatis tanggal 20.</p>
                    </div>

                    <div>
                        <label for="topup_amount_display" class="form-label">Nominal TopUp <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                            <input
                                id="topup_amount_display"
                                type="text"
                                inputmode="numeric"
                                x-model="topUpDisplay"
                                @input="formatTopUpInput"
                                @blur="formatTopUpInput"
                                class="form-control pl-10"
                                placeholder="0"
                                autocomplete="off">
                        </div>
                        <input type="hidden" name="topup_amount" :value="topUpRaw">
                        @error('topup_amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Sisa Pokok Pinjaman Lama</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                            <input type="text" :value="formatCurrency(outstanding)" class="form-control pl-10" disabled>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Total Nominal Pinjaman Baru</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                            <input type="text" :value="formatCurrency(totalPrincipal)" class="form-control pl-10 font-bold" disabled>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Nominal TopUp + Sisa Pokok. Nilai inilah yang disimpan sebagai nominal pinjaman baru.</p>
                    </div>

                    <div>
                        <label for="tenor_months" class="form-label">Tenor Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="tenor_months"
                                name="tenor_months"
                                type="number"
                                min="1"
                                required
                                value="{{ $initialTenor }}"
                                class="form-control pr-16">
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-500 dark:text-slate-400">bulan</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Batas tenor jenis pinjaman:
                            {{ $sourceLoan->loanType->min_tenor }}
                            @if($sourceLoan->loanType->max_tenor !== null)
                                - {{ $sourceLoan->loanType->max_tenor }} bulan
                            @else
                                bulan atau lebih
                            @endif
                        </p>
                        @error('tenor_months')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Catatan</label>
                        <textarea rows="5" class="form-textarea" readonly>{{ $notes }}</textarea>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Catatan dibuat otomatis dari data pinjaman lama dan tidak dapat diedit.</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <a href="{{ route('loans.show', $topUpLoan ?: $sourceLoan) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('topUpLoanForm', (config) => ({
                    topUpRaw: '',
                    topUpDisplay: '',
                    outstanding: Number(config.outstanding || 0),
                    init() {
                        if (config.initialTopUp !== null && config.initialTopUp !== undefined && String(config.initialTopUp) !== '') {
                            this.setTopUp(config.initialTopUp);
                        }
                    },
                    get totalPrincipal() {
                        return Number(this.topUpRaw || 0) + this.outstanding;
                    },
                    formatTopUpInput(event) {
                        const digits = String(event.target.value || '').replace(/[^\d]/g, '');
                        this.topUpRaw = digits ? String(parseInt(digits, 10)) : '';
                        this.topUpDisplay = this.topUpRaw ? this.formatCurrency(this.topUpRaw) : '';
                    },
                    setTopUp(value) {
                        const digits = String(value).split('.')[0].replace(/[^\d]/g, '');
                        this.topUpRaw = digits ? String(parseInt(digits, 10)) : '';
                        this.topUpDisplay = this.topUpRaw ? this.formatCurrency(this.topUpRaw) : '';
                    },
                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0));
                    },
                }));
            });
        </script>
    @endpush
</x-app-layout>
