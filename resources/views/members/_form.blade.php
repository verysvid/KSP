@csrf

<div
    x-data="memberAmountSaving(@js(old('amount_saving', isset($member) && $member->amount_saving !== null ? (float) $member->amount_saving : null)))"
    class="grid grid-cols-1 gap-5 md:grid-cols-2"
>
    {{-- Cabang --}}
    <div>
        <label for="branch_id" class="form-label">Cabang <span class="text-red-500">*</span></label>

        @if($isSuperAdmin)
            <select id="branch_id" name="branch_id" class="form-select" required>
                <option value="">Pilih Cabang</option>
                @foreach($branches as $branch)
                    <option
                        value="{{ $branch->id }}"
                        @selected((string) old('branch_id', isset($member) ? $member->branch_id : '') === (string) $branch->id)
                    >
                        {{ $branch->code }} - {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        @else
            <select id="branch_id_display" class="form-select cursor-not-allowed opacity-80" disabled>
                <option selected>{{ $currentBranch->code }} - {{ $currentBranch->name }}</option>
            </select>
        @endif

        @error('branch_id') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Jenis Anggota --}}
    <div>
        <label for="member_type_id" class="form-label">Jenis Anggota <span class="text-red-500">*</span></label>
        <select id="member_type_id" name="member_type_id" class="form-select" required>
            <option value="">Pilih Jenis Anggota</option>
            @foreach($memberTypes as $memberType)
                <option
                    value="{{ $memberType->id }}"
                    @selected((string) old('member_type_id', isset($member) ? $member->member_type_id : '') === (string) $memberType->id)
                >
                    {{ $memberType->name }}
                </option>
            @endforeach
        </select>
        @error('member_type_id') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Nama Anggota --}}
    <div>
        <label for="name" class="form-label">Nama Anggota <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" required maxlength="255"
            value="{{ old('name', $member->name ?? '') }}" class="form-control" placeholder="Nama lengkap anggota">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- NIK --}}
    <div>
        <label for="nik" class="form-label">NIK</label>
        <input id="nik" name="nik" type="text" maxlength="30"
            value="{{ old('nik', $member->nik ?? '') }}" class="form-control" placeholder="Nomor Induk Kependudukan">
        @error('nik') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Jenis Kelamin --}}
    <div>
        <label for="gender" class="form-label">Jenis Kelamin</label>
        <select id="gender" name="gender" class="form-select">
            <option value="">Pilih Jenis Kelamin</option>
            <option value="L" @selected(old('gender', $member->gender ?? '') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $member->gender ?? '') === 'P')>Perempuan</option>
        </select>
        @error('gender') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Tempat Lahir --}}
    <div>
        <label for="birth_place" class="form-label">Tempat Lahir</label>
        <input id="birth_place" name="birth_place" type="text" maxlength="255"
            value="{{ old('birth_place', $member->birth_place ?? '') }}" class="form-control" placeholder="Tempat lahir">
        @error('birth_place') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Tanggal Lahir --}}
    <div>
        <label for="birth_date" class="form-label">Tanggal Lahir</label>
        <input id="birth_date" name="birth_date" type="date"
            value="{{ old('birth_date', isset($member) && $member->birth_date ? $member->birth_date->format('Y-m-d') : '') }}"
            class="form-control">
        @error('birth_date') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Telepon --}}
    <div>
        <label for="phone" class="form-label">Telepon</label>
        <input id="phone" name="phone" type="text" maxlength="30"
            value="{{ old('phone', $member->phone ?? '') }}" class="form-control" placeholder="08xxxxxxxxxx">
        @error('phone') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" maxlength="255"
            value="{{ old('email', $member->email ?? '') }}" class="form-control" placeholder="nama@email.com">
        @error('email') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Pekerjaan --}}
    <div>
        <label for="occupation" class="form-label">Pekerjaan</label>
        <input id="occupation" name="occupation" type="text" maxlength="255"
            value="{{ old('occupation', $member->occupation ?? '') }}" class="form-control" placeholder="Pekerjaan anggota">
        @error('occupation') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Nominal Simpanan Manasuka --}}
    <div>
        <label for="amount_saving_display" class="form-label">Nominal Simpanan Manasuka</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Rp</span>
            <input
                id="amount_saving_display"
                type="text"
                inputmode="numeric"
                x-model="amountSavingDisplay"
                @input="formatAmountSavingInput"
                @blur="formatAmountSavingInput"
                class="form-control pl-10"
                placeholder="0"
                autocomplete="off"
            >
        </div>
        <input type="hidden" name="amount_saving" :value="amountSavingRaw">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Nominal default Simpanan Manasuka untuk anggota ini.
        </p>
        @error('amount_saving') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Tanggal Bergabung --}}
    <div>
        <label for="join_date" class="form-label">Tanggal Bergabung <span class="text-red-500">*</span></label>
        <input id="join_date" name="join_date" type="date" required
            value="{{ old('join_date', isset($member) && $member->join_date ? $member->join_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="form-control">
        @error('join_date') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Status --}}
    <div>
        <label for="member_status" class="form-label">Status <span class="text-red-500">*</span></label>
        <select id="member_status" name="member_status" class="form-select" required>
            <option value="ACTIVE" @selected(old('member_status', $member->member_status ?? 'ACTIVE') === 'ACTIVE')>Aktif</option>
            <option value="INACTIVE" @selected(old('member_status', $member->member_status ?? 'ACTIVE') === 'INACTIVE')>Tidak Aktif</option>
        </select>
        @error('member_status') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Alamat --}}
    <div class="md:col-span-2">
        <label for="address" class="form-label">Alamat</label>
        <textarea id="address" name="address" rows="3" class="form-textarea" placeholder="Alamat anggota">{{ old('address', $member->address ?? '') }}</textarea>
        @error('address') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- Catatan --}}
    <div class="md:col-span-2">
        <label for="notes" class="form-label">Catatan</label>
        <textarea id="notes" name="notes" rows="3" class="form-textarea" placeholder="Catatan tambahan">{{ old('notes', $member->notes ?? '') }}</textarea>
        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
    <a href="{{ route('members.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('memberAmountSaving', (initialAmount) => ({
                    amountSavingRaw: '',
                    amountSavingDisplay: '',

                    init() {
                        if (initialAmount !== null && initialAmount !== undefined && String(initialAmount) !== '') {
                            this.setAmountSaving(initialAmount);
                        }
                    },

                    formatAmountSavingInput(event) {
                        const digits = String(event.target.value || '').replace(/[^\d]/g, '');
                        this.amountSavingRaw = digits ? String(parseInt(digits, 10)) : '';
                        this.amountSavingDisplay = this.amountSavingRaw
                            ? this.formatCurrency(this.amountSavingRaw)
                            : '';
                    },

                    setAmountSaving(value) {
                        const numeric = Math.floor(Number(value || 0));
                        this.amountSavingRaw = numeric > 0 || Number(value) === 0 ? String(numeric) : '';
                        this.amountSavingDisplay = this.amountSavingRaw !== ''
                            ? this.formatCurrency(this.amountSavingRaw)
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
