<x-guest-layout wide>
    <div
        x-data="publicMemberRegistration(@js(old('amount_saving')))"
        class="mx-auto w-full"
    >
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                Formulir Permohonan Menjadi Anggota
            </h1>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Lengkapi data pemohon, lampiran KTP, serta pernyataan dan kesediaan anggota.
            </p>
        </div>

        <form
            id="member-registration-form"
            method="POST"
            action="{{ route('member-registration.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-5">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        I. Data Pribadi Pemohon
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Isi data sesuai identitas pemohon.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="branch_id" class="form-label">
                            Cabang <span class="text-red-500">*</span>
                        </label>
                        <select id="branch_id" name="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang</option>
                            @foreach($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected((string) old('branch_id') === (string) $branch->id)
                                >
                                    {{ $branch->code }} - {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="name" class="form-label">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            class="form-control"
                            maxlength="255"
                            required
                            autofocus
                        >
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nik" class="form-label">
                            NIK / No. KTP <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nik"
                            name="nik"
                            type="text"
                            value="{{ old('nik') }}"
                            class="form-control"
                            maxlength="30"
                            required
                        >
                        @error('nik') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birth_place" class="form-label">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="birth_place"
                            name="birth_place"
                            type="text"
                            value="{{ old('birth_place') }}"
                            class="form-control"
                            maxlength="255"
                            required
                        >
                        @error('birth_place') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birth_date" class="form-label">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="birth_date"
                            name="birth_date"
                            type="date"
                            value="{{ old('birth_date') }}"
                            class="form-control"
                            required
                        >
                        @error('birth_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gender" class="form-label">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                        @error('gender') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="form-label">
                            No. Telepon / WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone') }}"
                            class="form-control"
                            maxlength="30"
                            placeholder="08xxxxxxxxxx"
                            required
                        >
                        @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="form-label">
                            Alamat Domisili <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="form-textarea"
                            required
                        >{{ old('address') }}</textarea>
                        @error('address') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            class="form-control"
                            maxlength="255"
                            required
                        >
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Email digunakan sebagai akun login setelah aktivasi.
                        </p>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="occupation" class="form-label">
                            Pekerjaan / Jabatan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="occupation"
                            name="occupation"
                            type="text"
                            value="{{ old('occupation') }}"
                            class="form-control"
                            maxlength="255"
                            required
                        >
                        @error('occupation') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="work_unit" class="form-label">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="work_unit"
                            name="work_unit"
                            type="text"
                            value="{{ old('work_unit') }}"
                            class="form-control"
                            maxlength="255"
                            required
                        >
                        @error('work_unit') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="amount_saving_display" class="form-label">
                            Simpanan Sukarela / Manasuka per Bulan
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Rp
                            </span>
                            <input
                                id="amount_saving_display"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                class="form-control pl-10"
                                placeholder="0"
                                x-model="amountSavingDisplay"
                                @input="formatAmountSavingInput"
                                required
                            >
                        </div>
                        <input
                            id="amount_saving"
                            type="hidden"
                            name="amount_saving"
                            :value="amountSavingRaw"
                        >
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
                            value="{{ old('join_date', now()->format('Y-m-d')) }}"
                            class="form-control"
                            required
                        >
                        @error('join_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    II. Pernyataan dan Kesediaan Anggota
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Dengan mengisi dan mengirim formulir ini, saya menyatakan:
                </p>

                <ol class="mt-4 list-decimal space-y-4 pl-5 text-sm leading-6 text-slate-700 dark:text-slate-300">
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
                        Bersedia membayar kewajiban simpanan sebagai berikut:

                        <div class="mt-3 space-y-2 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                            <div>
                                Simpanan Pokok:
                                <strong>
                                    Rp {{ number_format((float) ($savingPokok?->amount ?? 0), 0, ',', '.') }}
                                </strong>
                                <span class="text-slate-500 dark:text-slate-400">
                                    (dibayar 1x saat mendaftar)
                                </span>
                            </div>

                            <div>
                                Simpanan Wajib:
                                <strong>
                                    Rp {{ number_format((float) ($savingWajib?->amount ?? 0), 0, ',', '.') }}
                                </strong>
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
                        Data yang saya isikan di atas adalah benar dan dapat dipertanggungjawabkan.
                    </li>
                </ol>

                <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <input
                        type="checkbox"
                        name="agreement"
                        value="1"
                        class="mt-1 rounded border-slate-300"
                        required
                        @checked(old('agreement'))
                    >
                    <span class="text-sm leading-6 text-slate-700 dark:text-slate-300">
                        Saya telah membaca, memahami, dan menyetujui pernyataan dan kesediaan anggota di atas.
                    </span>
                </label>
                @error('agreement') <p class="form-error">{{ $message }}</p> @enderror
            </section>

            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    III. Lampiran Persyaratan
                </h2>

                <div class="mt-4">
                    <label for="id_card_image" class="form-label">
                        Foto KTP / Kartu Identitas <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="id_card_image"
                        name="id_card_image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="form-control"
                        required
                        @change="previewKtp($event)"
                    >
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </p>

                    <div
                        x-show="ktpPreview"
                        x-cloak
                        class="mt-4 rounded-xl border border-slate-200 p-3 dark:border-slate-700"
                    >
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Preview KTP
                        </p>

                        <button
                            type="button"
                            class="block w-full"
                            @click="ktpModalOpen = true"
                        >
                            <img
                                :src="ktpPreview"
                                alt="Preview KTP"
                                class="mx-auto max-h-72 rounded-lg object-contain shadow-sm"
                            >
                        </button>

                        <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">
                            Klik gambar untuk memperbesar.
                        </p>
                    </div>

                    @error('id_card_image') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="mt-7">
                <button type="submit" class="btn btn-primary w-full">
                    Submit Registrasi
                </button>
            </div>

            <div class="mt-5 text-center text-sm text-slate-500 dark:text-slate-400">
                Sudah memiliki akun?
                <a
                    href="{{ route('login') }}"
                    class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                >
                    Login
                </a>
            </div>
        </form>
    </div>

    <script>
        function publicMemberRegistration(initialAmount) {
            return {
                amountSavingRaw: '',
                amountSavingDisplay: '',
                ktpPreview: null,
                ktpModalOpen: false,

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

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 0
                    }).format(Number(value || 0));
                }
            };
        }
    </script>

    <div
        x-show="ktpModalOpen"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="ktpModalOpen = false"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4"
        style="display: none;"
        @click.self="ktpModalOpen = false"
    >
        <div class="relative max-h-[92vh] w-full max-w-5xl overflow-auto rounded-2xl bg-white p-3 shadow-2xl dark:bg-slate-900">
            <button
                type="button"
                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-black/70 text-xl font-bold text-white hover:bg-black"
                @click="ktpModalOpen = false"
                aria-label="Tutup preview KTP"
            >
                &times;
            </button>

            <img
                :src="ktpPreview"
                alt="Preview KTP ukuran besar"
                class="mx-auto max-h-[86vh] w-auto rounded-xl object-contain"
            >
        </div>
    </div>

    @if (session('registration_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const redirectTo = @json(url('/'));

                if (typeof Swal === 'undefined') {
                    alert('Registrasi Berhasil\nHarap menunggu Aktivasi dari Pengurus.');
                    window.location.href = redirectTo;
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil',
                    text: 'Harap menunggu Aktivasi dari Pengurus.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = redirectTo;
                    }
                });
            });
        </script>
    @endif
</x-guest-layout>
