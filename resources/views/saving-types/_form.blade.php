@csrf

<div
    x-data="savingTypeForm(@js(old('amount', isset($savingType) ? $savingType->amount : null)))"
    class="grid grid-cols-1 gap-5 md:grid-cols-2"
>
    <div>
        <label for="code" class="form-label">
            Kode <span class="text-red-500">*</span>
        </label>

        <input
            id="code"
            name="code"
            type="text"
            required
            maxlength="30"
            value="{{ old('code', $savingType->code ?? '') }}"
            class="form-control"
            placeholder="POKOK"
        >

        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="form-label">
            Nama Jenis Simpanan <span class="text-red-500">*</span>
        </label>

        <input
            id="name"
            name="name"
            type="text"
            required
            maxlength="150"
            value="{{ old('name', $savingType->name ?? '') }}"
            class="form-control"
            placeholder="Simpanan Pokok"
        >

        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="form-label">Deskripsi</label>

        <textarea
            id="description"
            name="description"
            rows="3"
            class="form-textarea"
            placeholder="Keterangan jenis simpanan"
        >{{ old('description', $savingType->description ?? '') }}</textarea>

        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount_display" class="form-label">
            Nominal Default
        </label>

        <div class="relative">
            <span
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center
                       pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400"
            >
                Rp
            </span>

            <input
                id="amount_display"
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
            name="amount"
            :value="amountRaw"
        >

        @error('amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div></div>

    <div>
        <label class="form-label">Wajib</label>

        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <input
                type="checkbox"
                name="is_mandatory"
                value="1"
                @checked(old('is_mandatory', $savingType->is_mandatory ?? false))
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >

            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Jenis simpanan wajib
            </span>
        </label>
    </div>

    <div>
        <label class="form-label">Dapat Ditarik</label>

        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <input
                type="checkbox"
                name="is_withdrawable"
                value="1"
                @checked(old('is_withdrawable', $savingType->is_withdrawable ?? false))
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >

            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Saldo dapat ditarik
            </span>
        </label>

        @error('is_withdrawable')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="form-label">Status</label>

        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $savingType->is_active ?? true))
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >

            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Jenis simpanan aktif
            </span>
        </label>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
    <a href="{{ route('saving-types.index') }}" class="btn btn-secondary">
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
                Alpine.data('savingTypeForm', (initialAmount) => ({
                    amountRaw: '',
                    amountDisplay: '',

                    init() {
                        if (
                            initialAmount !== null &&
                            initialAmount !== undefined &&
                            String(initialAmount) !== ''
                        ) {
                            this.setAmount(initialAmount);
                        }
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
                    }
                }));
            });
        </script>
    @endpush
@endonce
