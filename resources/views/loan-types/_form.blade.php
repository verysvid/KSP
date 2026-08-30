@csrf

<div
    x-data="loanTypeForm({
        penaltyType: @js(old('penalty_type', $loanType->penalty_type ?? 'NONE'))
    })"
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
            value="{{ old('code', $loanType->code ?? '') }}"
            class="form-control"
            placeholder="PROD"
        >

        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="form-label">
            Nama Jenis Pinjaman <span class="text-red-500">*</span>
        </label>

        <input
            id="name"
            name="name"
            type="text"
            required
            maxlength="150"
            value="{{ old('name', $loanType->name ?? '') }}"
            class="form-control"
            placeholder="Pinjaman Produktif"
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
            placeholder="Keterangan jenis pinjaman"
        >{{ old('description', $loanType->description ?? '') }}</textarea>

        @error('description')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="interest_type" class="form-label">
            Metode Bunga <span class="text-red-500">*</span>
        </label>

        <select
            id="interest_type"
            name="interest_type"
            required
            class="form-select"
        >
            <option value="FLAT"
                @selected(old('interest_type', $loanType->interest_type ?? 'FLAT') === 'FLAT')>
                Flat
            </option>

            <option value="EFFECTIVE"
                @selected(old('interest_type', $loanType->interest_type ?? '') === 'EFFECTIVE')>
                Effective
            </option>
        </select>

        @error('interest_type')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="interest_rate" class="form-label">
            Bunga per Bulan (%) <span class="text-red-500">*</span>
        </label>

        <input
            id="interest_rate"
            name="interest_rate"
            type="number"
            min="0"
            max="100"
            step="0.0001"
            required
            value="{{ old('interest_rate', $loanType->interest_rate ?? 0) }}"
            class="form-control"
            placeholder="1.0000"
        >

        @error('interest_rate')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="min_amount_display" class="form-label">
            Minimum Pinjaman
        </label>

        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500 dark:text-gray-400">
                Rp
            </span>

            <input
                id="min_amount_display"
                type="text"
                inputmode="numeric"
                class="form-control pl-10"
                placeholder="0"
                data-currency-target="min_amount"
                value="{{ old('min_amount', $loanType->min_amount ?? '') !== '' ? number_format((float) old('min_amount', $loanType->min_amount ?? 0), 0, ',', '.') : '' }}"
            >

            <input
                id="min_amount"
                name="min_amount"
                type="hidden"
                value="{{ old('min_amount', $loanType->min_amount ?? '') }}"
            >
        </div>

        @error('min_amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="max_amount_display" class="form-label">
            Maksimum Pinjaman
        </label>

        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500 dark:text-gray-400">
                Rp
            </span>

            <input
                id="max_amount_display"
                type="text"
                inputmode="numeric"
                class="form-control pl-10"
                placeholder="0"
                data-currency-target="max_amount"
                value="{{ old('max_amount', $loanType->max_amount ?? '') !== '' ? number_format((float) old('max_amount', $loanType->max_amount ?? 0), 0, ',', '.') : '' }}"
            >

            <input
                id="max_amount"
                name="max_amount"
                type="hidden"
                value="{{ old('max_amount', $loanType->max_amount ?? '') }}"
            >
        </div>

        @error('max_amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="min_tenor" class="form-label">
            Minimum Tenor (Bulan) <span class="text-red-500">*</span>
        </label>

        <input
            id="min_tenor"
            name="min_tenor"
            type="number"
            min="1"
            max="600"
            required
            value="{{ old('min_tenor', $loanType->min_tenor ?? 1) }}"
            class="form-control"
            placeholder="1"
        >

        @error('min_tenor')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="max_tenor" class="form-label">
            Maksimum Tenor (Bulan)
        </label>

        <input
            id="max_tenor"
            name="max_tenor"
            type="number"
            min="1"
            max="600"
            value="{{ old('max_tenor', $loanType->max_tenor ?? '') }}"
            class="form-control"
            placeholder="36"
        >

        @error('max_tenor')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="penalty_type" class="form-label">
            Tipe Denda <span class="text-red-500">*</span>
        </label>

        <select
            id="penalty_type"
            name="penalty_type"
            required
            x-model="penaltyType"
            class="form-select"
        >
            <option value="NONE">Tidak Ada</option>
            <option value="FIXED">Nominal Tetap</option>
            <option value="PERCENTAGE">Persentase</option>
        </select>

        @error('penalty_type')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="penaltyType === 'FIXED'" x-cloak>
        <label for="penalty_amount_display" class="form-label">
            Nominal Denda
        </label>

        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500 dark:text-gray-400">
                Rp
            </span>

            <input
                id="penalty_amount_display"
                type="text"
                inputmode="numeric"
                class="form-control pl-10"
                placeholder="0"
                data-currency-target="penalty_amount"
                value="{{ old('penalty_amount', $loanType->penalty_amount ?? '') !== '' ? number_format((float) old('penalty_amount', $loanType->penalty_amount ?? 0), 0, ',', '.') : '' }}"
            >

            <input
                id="penalty_amount"
                name="penalty_amount"
                type="hidden"
                value="{{ old('penalty_amount', $loanType->penalty_amount ?? '') }}"
            >
        </div>

        @error('penalty_amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="penaltyType === 'PERCENTAGE'" x-cloak>
        <label for="penalty_rate" class="form-label">
            Persentase Denda (%)
        </label>

        <input
            id="penalty_rate"
            name="penalty_rate"
            type="number"
            min="0"
            max="100"
            step="0.0001"
            value="{{ old('penalty_rate', $loanType->penalty_rate ?? '') }}"
            class="form-control"
            placeholder="0.1000"
        >

        @error('penalty_rate')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex cursor-pointer items-center gap-3">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800"
                @checked(old('is_active', $loanType->is_active ?? true))
            >

            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Jenis pinjaman aktif
            </span>
        </label>

        @error('is_active')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    function loanTypeForm(config = {}) {
        return {
            penaltyType: config.penaltyType || 'NONE',
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-currency-target]').forEach(function (input) {
            const targetId = input.dataset.currencyTarget;
            const hidden = document.getElementById(targetId);

            if (!hidden) {
                return;
            }

            const formatCurrency = function (value) {
                const numeric = String(value || '').replace(/\D/g, '');

                if (!numeric) {
                    return '';
                }

                return new Intl.NumberFormat('id-ID').format(Number(numeric));
            };

            const syncValue = function () {
                const numeric = input.value.replace(/\D/g, '');

                hidden.value = numeric;
                input.value = formatCurrency(numeric);
            };

            input.addEventListener('input', syncValue);
            input.value = formatCurrency(input.value);
        });
    });
</script>
@endpush
