@csrf

<div
    x-data="memberAmountSaving(@js(old('amount_saving', isset($member) && $member->amount_saving !== null ? (float) $member->amount_saving : null)))"
    class="space-y-6"
>
    <section>
        <div class="mb-4">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                I. Data Pribadi Anggota
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="branch_id" class="form-label">
                    Cabang <span class="text-red-500">*</span>
                </label>

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

            <div>
                <label for="member_type_id" class="form-label">
                    Jenis Anggota <span class="text-red-500">*</span>
                </label>
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

            <div>
                <label for="name" class="form-label">
                    Nama Anggota <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name', $member->name ?? '') }}"
                    class="form-control"
                    placeholder="Nama lengkap anggota"
                >
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nik" class="form-label">NIK / No. KTP</label>
                <input
                    id="nik"
                    name="nik"
                    type="text"
                    maxlength="30"
                    value="{{ old('nik', $member->nik ?? '') }}"
                    class="form-control"
                    placeholder="Nomor Induk Kependudukan"
                >
                @error('nik') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="gender" class="form-label">Jenis Kelamin</label>
                <select id="gender" name="gender" class="form-select">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L" @selected(old('gender', $member->gender ?? '') === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('gender', $member->gender ?? '') === 'P')>Perempuan</option>
                </select>
                @error('gender') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="birth_place" class="form-label">Tempat Lahir</label>
                <input
                    id="birth_place"
                    name="birth_place"
                    type="text"
                    maxlength="255"
                    value="{{ old('birth_place', $member->birth_place ?? '') }}"
                    class="form-control"
                    placeholder="Tempat lahir"
                >
                @error('birth_place') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="birth_date" class="form-label">Tanggal Lahir</label>
                <input
                    id="birth_date"
                    name="birth_date"
                    type="date"
                    value="{{ old('birth_date', isset($member) && $member->birth_date ? $member->birth_date->format('Y-m-d') : '') }}"
                    class="form-control"
                >
                @error('birth_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="form-label">Telepon / WhatsApp</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    maxlength="30"
                    value="{{ old('phone', $member->phone ?? '') }}"
                    class="form-control"
                    placeholder="08xxxxxxxxxx"
                >
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="form-label">
                    Email <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    maxlength="255"
                    required
                    value="{{ old('email', $member->email ?? '') }}"
                    class="form-control"
                    placeholder="nama@email.com"
                >
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="occupation" class="form-label">Pekerjaan / Jabatan</label>
                <input
                    id="occupation"
                    name="occupation"
                    type="text"
                    maxlength="255"
                    value="{{ old('occupation', $member->occupation ?? '') }}"
                    class="form-control"
                    placeholder="Pekerjaan / jabatan anggota"
                >
                @error('occupation') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="work_unit" class="form-label">Unit Kerja</label>
                <input
                    id="work_unit"
                    name="work_unit"
                    type="text"
                    maxlength="255"
                    value="{{ old('work_unit', $member->work_unit ?? '') }}"
                    class="form-control"
                    placeholder="Unit kerja anggota"
                >
                @error('work_unit') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="amount_saving_display" class="form-label">
                    Nominal Simpanan Sukarela / Manasuka
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Rp
                    </span>
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
                    Nominal default Simpanan Sukarela / Manasuka untuk anggota ini.
                </p>
                @error('amount_saving') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="join_date" class="form-label">
                    Tanggal Bergabung <span class="text-red-500">*</span>
                </label>
                <input
                    id="join_date"
                    name="join_date"
                    type="date"
                    required
                    value="{{ old('join_date', isset($member) && $member->join_date ? $member->join_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="form-control"
                >
                @error('join_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            @if(isset($member))
                <div>
                    <label for="member_status" class="form-label">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="member_status" name="member_status" class="form-select" required>
                        <option value="ACTIVE" @selected(old('member_status', $member->member_status) === 'ACTIVE')>Aktif</option>
                        <option value="INACTIVE" @selected(old('member_status', $member->member_status) === 'INACTIVE')>Tidak Aktif</option>
                    </select>
                    @error('member_status') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            @else
                <input type="hidden" name="member_status" value="NEW">
            @endif

            <div class="md:col-span-2">
                <label for="address" class="form-label">Alamat</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="form-textarea"
                    placeholder="Alamat anggota"
                >{{ old('address', $member->address ?? '') }}</textarea>
                @error('address') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-700 sm:p-5">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
            II. Pernyataan dan Kesediaan Anggota
        </h3>

        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Dengan mengisi formulir ini, anggota menyatakan:
        </p>

        <ol class="mt-3 list-decimal space-y-3 pl-5 text-sm leading-6 text-slate-700 dark:text-slate-300">
            <li>
                <strong>Keinginan Menjadi Anggota:</strong>
                Mengajukan diri secara sukarela untuk menjadi Anggota Koperasi Pegawai BPPT Kabupaten Bekasi
                (Mahabah Bersama Sejahtera).
            </li>
            <li>
                <strong>Kepatuhan Peraturan:</strong>
                Bersedia mematuhi seluruh ketentuan Anggaran Dasar (AD), Anggaran Rumah Tangga (ART),
                serta Keputusan Rapat Anggota Koperasi.
            </li>
            <li>
                <strong>Kewajiban Keuangan:</strong>
                <div class="mt-2 space-y-1 rounded-lg bg-slate-50 p-3 dark:bg-slate-800/60">
                    <div>
                        Simpanan Pokok:
                        <strong>Rp {{ number_format((float) ($savingPokok?->amount ?? 0), 0, ',', '.') }}</strong>
                        (dibayar 1x saat mendaftar)
                    </div>
                    <div>
                        Simpanan Wajib:
                        <strong>Rp {{ number_format((float) ($savingWajib?->amount ?? 0), 0, ',', '.') }}</strong>
                        / bulan
                    </div>
                    <div>
                        Simpanan Sukarela:
                        <strong>Rp <span x-text="amountSavingDisplay || '0'"></span></strong>
                        / bulan
                    </div>
                </div>
            </li>
            <li>
                <strong>Kebenaran Data:</strong>
                Data yang diisikan adalah benar dan dapat dipertanggungjawabkan.
            </li>
        </ol>

        @unless(isset($member))
            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                <input
                    type="checkbox"
                    name="agreement"
                    value="1"
                    class="mt-1 rounded border-slate-300"
                    required
                    @checked(old('agreement'))
                >
                <span class="text-sm leading-6 text-slate-700 dark:text-slate-300">
                    Saya mengonfirmasi bahwa anggota telah membaca dan menyetujui pernyataan di atas.
                </span>
            </label>
            @error('agreement') <p class="form-error">{{ $message }}</p> @enderror
        @endunless
    </section>

    <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-700 sm:p-5">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
            III. Lampiran Persyaratan
        </h3>

        <div class="mt-4">
            <label for="id_card_image" class="form-label">
                Foto KTP / Kartu Identitas
            </label>
            <input
                id="id_card_image"
                name="id_card_image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="form-control"
                @change="previewKtp($event)"
            >
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                @if(isset($member) && $member->id_card_image)
                    Kosongkan jika tidak ingin mengganti file KTP yang sudah tersimpan.
                @endif
            </p>

            @if(isset($member) && $member->id_card_image)
                <div
                    x-show="!ktpPreview"
                    class="mt-4 rounded-xl border border-slate-200 p-3 dark:border-slate-700"
                >
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        KTP Tersimpan
                    </p>

                    <button
                        type="button"
                        class="block w-full"
                        @click="openKtpModal(@js(asset('storage/' . $member->id_card_image)))"
                    >
                        <img
                            src="{{ asset('storage/' . $member->id_card_image) }}"
                            alt="KTP {{ $member->name }}"
                            class="mx-auto max-h-64 rounded-lg object-contain shadow-sm"
                        >
                    </button>

                    <div class="mt-3 flex flex-wrap items-center justify-center gap-3">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="openKtpModal(@js(asset('storage/' . $member->id_card_image)))"
                        >
                            Lihat KTP
                        </button>

                        <a
                            href="{{ asset('storage/' . $member->id_card_image) }}"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-secondary"
                        >
                            Buka Tab Baru
                        </a>
                    </div>
                </div>
            @endif

            <div
                x-show="ktpPreview"
                x-cloak
                class="mt-4 rounded-xl border border-indigo-200 bg-indigo-50/50 p-3 dark:border-indigo-500/30 dark:bg-indigo-500/5"
            >
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">
                    Preview KTP Baru
                </p>

                <button
                    type="button"
                    class="block w-full"
                    @click="openKtpModal(ktpPreview)"
                >
                    <img
                        :src="ktpPreview"
                        alt="Preview KTP baru"
                        class="mx-auto max-h-64 rounded-lg object-contain shadow-sm"
                    >
                </button>

                <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">
                    Klik gambar untuk memperbesar.
                </p>
            </div>

            @error('id_card_image') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </section>

    <section>
        <label for="notes" class="form-label">Catatan</label>
        <textarea
            id="notes"
            name="notes"
            rows="3"
            class="form-textarea"
            placeholder="Catatan tambahan"
        >{{ old('notes', $member->notes ?? '') }}</textarea>
        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
    </section>
    <div
        x-show="ktpModalOpen"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="closeKtpModal()"
        @click.self="closeKtpModal()"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4"
        style="display: none;"
    >
        <div class="relative max-h-[92vh] w-full max-w-5xl overflow-auto rounded-2xl bg-white p-3 shadow-2xl dark:bg-slate-900">
            <button
                type="button"
                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-black/70 text-xl font-bold text-white hover:bg-black"
                @click="closeKtpModal()"
                aria-label="Tutup preview KTP"
            >
                &times;
            </button>

            <img
                :src="ktpModalSrc"
                alt="KTP ukuran besar"
                class="mx-auto max-h-[86vh] w-auto rounded-xl object-contain"
            >
        </div>
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
                    ktpPreview: null,
                    ktpModalOpen: false,
                    ktpModalSrc: null,

                    init() {
                        if (
                            initialAmount !== null
                            && initialAmount !== undefined
                            && String(initialAmount) !== ''
                        ) {
                            this.setAmountSaving(initialAmount);
                        }
                    },

                    formatAmountSavingInput(event) {
                        const digits = String(event.target.value || '')
                            .replace(/[^\d]/g, '');

                        this.amountSavingRaw = digits
                            ? String(parseInt(digits, 10))
                            : '';

                        this.amountSavingDisplay = this.amountSavingRaw
                            ? this.formatCurrency(this.amountSavingRaw)
                            : '';
                    },

                    setAmountSaving(value) {
                        const numeric = Math.floor(Number(value || 0));

                        this.amountSavingRaw = numeric > 0 || Number(value) === 0
                            ? String(numeric)
                            : '';

                        this.amountSavingDisplay = this.amountSavingRaw !== ''
                            ? this.formatCurrency(this.amountSavingRaw)
                            : '';
                    },

                    previewKtp(event) {
                        const file = event.target.files?.[0];

                        if (!file) {
                            this.ktpPreview = null;
                            return;
                        }

                        if (!file.type.startsWith('image/')) {
                            this.ktpPreview = null;
                            return;
                        }

                        const reader = new FileReader();

                        reader.onload = (e) => {
                            this.ktpPreview = e.target.result;
                        };

                        reader.readAsDataURL(file);
                    },

                    openKtpModal(src) {
                        this.ktpModalSrc = src;
                        this.ktpModalOpen = true;
                    },

                    closeKtpModal() {
                        this.ktpModalOpen = false;
                        this.ktpModalSrc = null;
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
