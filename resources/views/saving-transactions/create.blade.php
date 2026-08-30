<x-app-layout>
    <x-slot name="title">Transaksi Simpanan Baru</x-slot>

    <x-page-header
        title="Transaksi Simpanan Baru"
        description="Buat setoran atau penarikan simpanan anggota." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Transaksi"
            description="Transaksi baru akan berstatus PENDING sampai disetujui.">

            <form
                method="POST"
                action="{{ route('saving-transactions.store') }}"
                x-data="savingTransactionForm(
                    @js($members->map(fn ($member) => [
                        'id' => $member->id,
                        'member_number' => $member->member_number,
                        'name' => $member->name,
                        'label' => $member->member_number . ' - ' . $member->name,
                    ])->values()),
                    @js($savingTypes->map(fn ($type) => [
                        'id' => $type->id,
                        'code' => strtoupper($type->code),
                        'name' => $type->name,
                        'amount' => $type->amount !== null ? (float) $type->amount : null,
                        'is_mandatory' => (bool) $type->is_mandatory,
                        'is_withdrawable' => (bool) $type->is_withdrawable,
                    ])->values()),
                    @js(old('member_id')),
                    @js(old('saving_type_id')),
                    @js(old('amount'))
                )"
                @submit="prepareSubmit"
            >
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div class="relative md:col-span-2">
                        <label for="member_search" class="form-label">
                            Anggota <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="member_search"
                            type="text"
                            x-model="memberSearch"
                            @input="memberOpen = true; memberId = ''"
                            @focus="memberOpen = true"
                            @keydown.escape="memberOpen = false"
                            @keydown.arrow-down.prevent="moveMember(1)"
                            @keydown.arrow-up.prevent="moveMember(-1)"
                            @keydown.enter.prevent="selectHighlightedMember()"
                            class="form-control"
                            placeholder="Ketik nomor anggota atau nama anggota..."
                            autocomplete="off"
                        >

                        <input type="hidden" name="member_id" :value="memberId">

                        <div
                            x-show="memberOpen"
                            x-transition
                            @click.outside="memberOpen = false"
                            x-cloak
                            class="absolute z-40 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800"
                        >
                            <template x-if="filteredMembers.length === 0">
                                <div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    Anggota tidak ditemukan.
                                </div>
                            </template>

                            <template x-for="(member, index) in filteredMembers" :key="member.id">
                                <button
                                    type="button"
                                    @click="selectMember(member)"
                                    @mouseenter="memberHighlight = index"
                                    class="block w-full border-b border-slate-100 px-4 py-3 text-left last:border-b-0 hover:bg-indigo-50 dark:border-slate-700 dark:hover:bg-slate-700"
                                    :class="memberHighlight === index ? 'bg-indigo-50 dark:bg-slate-700' : ''"
                                >
                                    <div class="font-semibold text-slate-900 dark:text-white" x-text="member.member_number"></div>
                                    <div class="mt-0.5 text-sm text-slate-500 dark:text-slate-400" x-text="member.name"></div>
                                </button>
                            </template>
                        </div>

                        @error('member_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="saving_type_id" class="form-label">
                            Jenis Simpanan <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="saving_type_id"
                            name="saving_type_id"
                            x-model="savingTypeId"
                            @change="savingTypeChanged"
                            required
                            class="form-select"
                        >
                            <option value="">Pilih Jenis Simpanan</option>
                            @foreach($savingTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->code }} - {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        <template x-if="selectedSavingType">
                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                <span x-text="savingTypeInfo"></span>
                            </div>
                        </template>

                        @error('saving_type_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transaction_type" class="form-label">
                            Jenis Transaksi <span class="text-red-500">*</span>
                        </label>

                        <select id="transaction_type" name="transaction_type" required class="form-select">
                            <option value="SETORAN" @selected(old('transaction_type', 'SETORAN') === 'SETORAN')>Setoran</option>
                            <option value="PENARIKAN" @selected(old('transaction_type') === 'PENARIKAN')>Penarikan</option>
                        </select>

                        @error('transaction_type')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transaction_date" class="form-label">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="transaction_date"
                            name="transaction_date"
                            type="date"
                            required
                            value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                            class="form-control"
                        >

                        @error('transaction_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount_display" class="form-label">
                            Nominal <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Rp
                            </span>

                            <input
                                id="amount_display"
                                type="text"
                                inputmode="numeric"
                                x-model="amountDisplay"
                                @input="formatAmountInput"
                                @blur="formatAmountInput"
                                required
                                class="form-control pl-10"
                                placeholder="0"
                                autocomplete="off"
                            >
                        </div>

                        <input type="hidden" name="amount" :value="amountRaw">

                        <template x-if="fixedAmount">
                            <p class="mt-1 text-xs text-indigo-600 dark:text-indigo-400">
                                Nominal otomatis mengikuti Master Jenis Simpanan.
                            </p>
                        </template>

                        @error('amount')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="remarks" class="form-label">Keterangan</label>
                        <textarea id="remarks" name="remarks" rows="3" class="form-textarea">{{ old('remarks') }}</textarea>

                        @error('remarks')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <a href="{{ route('saving-transactions.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('savingTransactionForm', (
                    members,
                    savingTypes,
                    oldMemberId,
                    oldSavingTypeId,
                    oldAmount
                ) => ({
                    members,
                    savingTypes,

                    memberId: oldMemberId ? String(oldMemberId) : '',
                    memberSearch: '',
                    memberOpen: false,
                    memberHighlight: 0,

                    savingTypeId: oldSavingTypeId ? String(oldSavingTypeId) : '',
                    amountRaw: '',
                    amountDisplay: '',
                    fixedAmount: false,

                    init() {
                        if (this.memberId) {
                            const member = this.members.find(
                                item => String(item.id) === this.memberId
                            );

                            if (member) {
                                this.memberSearch = member.label;
                            }
                        }

                        if (oldAmount !== null && oldAmount !== undefined && String(oldAmount) !== '') {
                            this.setAmount(oldAmount);
                        } else {
                            this.savingTypeChanged();
                        }
                    },

                    get filteredMembers() {
                        const keyword = this.memberSearch.toLowerCase().trim();

                        if (!keyword) {
                            return this.members.slice(0, 15);
                        }

                        return this.members
                            .filter(member =>
                                String(member.member_number).toLowerCase().includes(keyword) ||
                                String(member.name).toLowerCase().includes(keyword)
                            )
                            .slice(0, 25);
                    },

                    selectMember(member) {
                        this.memberId = String(member.id);
                        this.memberSearch = member.label;
                        this.memberOpen = false;
                        this.memberHighlight = 0;
                    },

                    moveMember(step) {
                        if (!this.memberOpen) this.memberOpen = true;

                        const total = this.filteredMembers.length;
                        if (!total) return;

                        this.memberHighlight += step;

                        if (this.memberHighlight < 0) this.memberHighlight = total - 1;
                        if (this.memberHighlight >= total) this.memberHighlight = 0;
                    },

                    selectHighlightedMember() {
                        const member = this.filteredMembers[this.memberHighlight];
                        if (member) this.selectMember(member);
                    },

                    get selectedSavingType() {
                        return this.savingTypes.find(
                            item => String(item.id) === String(this.savingTypeId)
                        ) || null;
                    },

                    get savingTypeInfo() {
                        const type = this.selectedSavingType;
                        if (!type) return '';

                        const info = [];

                        if (type.is_mandatory) info.push('Wajib');
                        info.push(type.is_withdrawable ? 'Dapat ditarik' : 'Tidak dapat ditarik');

                        if (type.amount !== null) {
                            info.push('Nominal Master Rp ' + this.formatCurrency(type.amount));
                        }

                        return info.join(' • ');
                    },

                    savingTypeChanged() {
                        const type = this.selectedSavingType;
                        this.fixedAmount = false;

                        if (!type) return;

                        const code = String(type.code).toUpperCase();

                        if ((code === 'POKOK' || code === 'WAJIB') && type.amount !== null) {
                            this.fixedAmount = true;
                            this.setAmount(type.amount);
                        }
                    },

                    formatAmountInput(event) {
                        const digits = String(event.target.value || '').replace(/[^\d]/g, '');

                        this.amountRaw = digits ? String(parseInt(digits, 10)) : '';
                        this.amountDisplay = this.amountRaw ? this.formatCurrency(this.amountRaw) : '';
                    },

                    setAmount(value) {
                        const numeric = String(value).replace(/[^\d]/g, '');

                        this.amountRaw = numeric ? String(parseInt(numeric, 10)) : '';
                        this.amountDisplay = this.amountRaw ? this.formatCurrency(this.amountRaw) : '';
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            maximumFractionDigits: 0
                        }).format(Number(value || 0));
                    },

                    prepareSubmit(event) {
                        if (!this.memberId) {
                            event.preventDefault();

                            if (window.swalError) {
                                window.swalError('Silakan pilih anggota terlebih dahulu.');
                            }

                            return;
                        }

                        if (!this.amountRaw || Number(this.amountRaw) <= 0) {
                            event.preventDefault();

                            if (window.swalError) {
                                window.swalError('Nominal transaksi harus lebih besar dari 0.');
                            }
                        }
                    }
                }));
            });
        </script>
    @endpush
</x-app-layout>
