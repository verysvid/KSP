@csrf

@php
    $selectedBranchId = old(
        'branch_id',
        isset($loan)
            ? $loan->branch_id
            : request('branch_id')
    );

    $selectedLoanTypeId = old(
        'loan_type_id',
        $loan->loan_type_id ?? ''
    );

    $selectedLoanType = $loanTypes->firstWhere(
        'id',
        (int) $selectedLoanTypeId
    );

    $initialAmount = old(
        'principal_amount',
        $loan->principal_amount ?? null
    );

    $isSuperAdmin = auth()->user()?->hasRole('SuperAdmin') ?? false;
@endphp

<div
    x-data="loanForm({
        initialAmount: @js($initialAmount),
        initialLoanTypeId: @js((string) $selectedLoanTypeId),
        loanTypes: @js(
            $loanTypes->map(fn ($type) => [
                'id' => (string) $type->id,
                'code' => $type->code,
                'name' => $type->name,
                'interest_type' => $type->interest_type,
                'interest_rate' => (string) $type->interest_rate,
                'min_amount' => $type->min_amount !== null ? (string) $type->min_amount : null,
                'max_amount' => $type->max_amount !== null ? (string) $type->max_amount : null,
                'min_tenor' => (int) $type->min_tenor,
                'max_tenor' => $type->max_tenor !== null ? (int) $type->max_tenor : null,
            ])->values()
        )
    })"
    class="grid grid-cols-1 gap-5 md:grid-cols-2"
>
    @if($isSuperAdmin)
        <div>
            <label for="branch_id" class="form-label">
                Cabang <span class="text-red-500">*</span>
            </label>

            <select
                id="branch_id"
                name="branch_id"
                required
                class="form-select"
                @if(!isset($loan))
                    onchange="
                        const value = this.value;
                        if (value) {
                            window.location.href = '{{ route('loans.create') }}?branch_id=' + encodeURIComponent(value);
                        }
                    "
                @endif
            >
                <option value="">Pilih Cabang</option>

                @foreach($branches as $branch)
                    <option
                        value="{{ $branch->id }}"
                        @selected((string) $selectedBranchId === (string) $branch->id)
                    >
                        {{ $branch->code }} - {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            @if(!isset($loan))
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Pilih cabang terlebih dahulu untuk menampilkan anggota aktif.
                </p>
            @endif

            @error('branch_id')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div>
            <label class="form-label">Cabang</label>

            <input
                type="text"
                value="{{ auth()->user()?->branch?->code ? auth()->user()->branch->code . ' - ' . auth()->user()->branch->name : (auth()->user()?->branch?->name ?? '-') }}"
                class="form-control"
                disabled
            >
        </div>
    @endif

    <div>
        <label for="application_date" class="form-label">
            Tanggal Pengajuan <span class="text-red-500">*</span>
        </label>

        <input
            id="application_date"
            name="application_date"
            type="date"
            required
            value="{{ old('application_date', isset($loan) && $loan->application_date ? $loan->application_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="form-control"
        >

        @error('application_date')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="member_id" class="form-label">
            Anggota <span class="text-red-500">*</span>
        </label>

        <select
            id="member_id"
            name="member_id"
            required
            class="form-select"
            @disabled($isSuperAdmin && !$selectedBranchId)
        >
            <option value="">Pilih Anggota</option>

            @foreach($members as $member)
                <option
                    value="{{ $member->id }}"
                    @selected((string) old('member_id', $loan->member_id ?? '') === (string) $member->id)
                >
                    {{ $member->name }}
                </option>
            @endforeach
        </select>

        @if($isSuperAdmin && !$selectedBranchId)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Anggota tersedia setelah cabang dipilih.
            </p>
        @endif

        @error('member_id')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="loan_type_id" class="form-label">
            Jenis Pinjaman <span class="text-red-500">*</span>
        </label>

        <select
            id="loan_type_id"
            name="loan_type_id"
            required
            x-model="loanTypeId"
            class="form-select"
        >
            <option value="">Pilih Jenis Pinjaman</option>

            @foreach($loanTypes as $loanType)
                <option
                    value="{{ $loanType->id }}"
                    @selected((string) $selectedLoanTypeId === (string) $loanType->id)
                >
                    {{ $loanType->code }} - {{ $loanType->name }}
                </option>
            @endforeach
        </select>

        @error('loan_type_id')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Metode & Bunga Default</label>

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                    dark:border-slate-700 dark:bg-slate-800/60">
            <template x-if="selectedLoanType">
                <div>
                    <div class="font-semibold text-slate-800 dark:text-slate-100">
                        <span x-text="selectedLoanType.interest_type"></span>
                        ·
                        <span x-text="formatRate(selectedLoanType.interest_rate)"></span>%
                    </div>

                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Nilai bunga akan disalin ke transaksi pinjaman.
                    </div>
                </div>
            </template>

            <template x-if="!selectedLoanType">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    Pilih jenis pinjaman.
                </span>
            </template>
        </div>
    </div>

    <div>
        <label for="principal_amount_display" class="form-label">
            Nominal Pinjaman <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <span
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center
                       pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400"
            >
                Rp
            </span>

            <input
                id="principal_amount_display"
                type="text"
                inputmode="numeric"
                x-model="amountDisplay"
                @input="formatAmountInput"
                @blur="formatAmountInput"
                class="form-control pl-10"
                placeholder="0"
                autocomplete="off"
            >
        </div>

        <input
            type="hidden"
            name="principal_amount"
            :value="amountRaw"
        >

        <template x-if="selectedLoanType">
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Batas:
                <span x-text="amountLimitText"></span>
            </p>
        </template>

        @error('principal_amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tenor_months" class="form-label">
            Tenor <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <input
                id="tenor_months"
                name="tenor_months"
                type="number"
                min="1"
                required
                value="{{ old('tenor_months', $loan->tenor_months ?? '') }}"
                class="form-control pr-16"
                placeholder="12"
            >

            <span
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center
                       pr-3 text-sm text-slate-500 dark:text-slate-400"
            >
                bulan
            </span>
        </div>

        <template x-if="selectedLoanType">
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Tenor jenis pinjaman:
                <span x-text="tenorLimitText"></span>
            </p>
        </template>

        @error('tenor_months')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="due_day" class="form-label">
            Tanggal Jatuh Tempo Angsuran <span class="text-red-500">*</span>
        </label>

        <select
            id="due_day"
            name="due_day"
            required
            class="form-select"
        >
            <option value="">Pilih Tanggal</option>

            @for($day = 1; $day <= 28; $day++)
                <option
                    value="{{ $day }}"
                    @selected((string) old('due_day', $loan->due_day ?? '') === (string) $day)
                >
                    Tanggal {{ $day }}
                </option>
            @endfor
        </select>

        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Angsuran pertama akan jatuh tempo pada tanggal ini di bulan setelah pencairan.
        </p>

        @error('due_day')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="form-label">Catatan</label>

        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="form-textarea"
            placeholder="Catatan atau keterangan pengajuan pinjaman"
        >{{ old('notes', $loan->notes ?? '') }}</textarea>

        @error('notes')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5
            sm:flex-row sm:justify-end dark:border-slate-800">
    <a href="{{ route('loans.index') }}" class="btn btn-secondary">
        Batal
    </a>

    <button type="submit" class="btn btn-primary">
        {{ $submitLabel }}
    </button>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('loanForm', (config) => ({
                    amountRaw: '',
                    amountDisplay: '',
                    loanTypeId: String(config.initialLoanTypeId || ''),
                    loanTypes: config.loanTypes || [],

                    init() {
                        if (
                            config.initialAmount !== null &&
                            config.initialAmount !== undefined &&
                            String(config.initialAmount) !== ''
                        ) {
                            this.setAmount(config.initialAmount);
                        }
                    },

                    get selectedLoanType() {
                        return this.loanTypes.find(
                            (item) => String(item.id) === String(this.loanTypeId)
                        ) || null;
                    },

                    get amountLimitText() {
                        if (!this.selectedLoanType) {
                            return '-';
                        }

                        const min = this.selectedLoanType.min_amount;
                        const max = this.selectedLoanType.max_amount;

                        if (min !== null && max !== null) {
                            return `Rp ${this.formatCurrency(min)} s/d Rp ${this.formatCurrency(max)}`;
                        }

                        if (min !== null) {
                            return `Minimal Rp ${this.formatCurrency(min)}`;
                        }

                        if (max !== null) {
                            return `Maksimal Rp ${this.formatCurrency(max)}`;
                        }

                        return 'Tidak dibatasi';
                    },

                    get tenorLimitText() {
                        if (!this.selectedLoanType) {
                            return '-';
                        }

                        const min = this.selectedLoanType.min_tenor;
                        const max = this.selectedLoanType.max_tenor;

                        if (max !== null) {
                            return `${min} - ${max} bulan`;
                        }

                        return `Minimal ${min} bulan`;
                    },

                    formatAmountInput(event) {
                        const digits = String(event.target.value || '')
                            .replace(/[^\d]/g, '');

                        this.amountRaw = digits
                            ? String(parseInt(digits, 10))
                            : '';

                        this.amountDisplay = this.amountRaw
                            ? this.formatCurrency(this.amountRaw)
                            : '';
                    },

                    setAmount(value) {
                        const digits = String(value)
                            .split('.')[0]
                            .replace(/[^\d]/g, '');

                        this.amountRaw = digits
                            ? String(parseInt(digits, 10))
                            : '';

                        this.amountDisplay = this.amountRaw
                            ? this.formatCurrency(this.amountRaw)
                            : '';
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            maximumFractionDigits: 0
                        }).format(Number(value || 0));
                    },

                    formatRate(value) {
                        return new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 4
                        }).format(Number(value || 0));
                    }
                }));
            });
        </script>
    @endpush
@endonce
