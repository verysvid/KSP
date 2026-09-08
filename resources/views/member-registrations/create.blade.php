<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
            Registrasi Anggota
        </h1>

        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Lengkapi data berikut untuk mendaftar sebagai anggota koperasi.
        </p>
    </div>

    <form
        id="member-registration-form"
        method="POST"
        action="{{ route('member-registration.store') }}"
    >
        @csrf

        <div class="space-y-5">
            {{-- Cabang --}}
            <div>
                <label for="branch_id" class="form-label">
                    Cabang <span class="text-red-500">*</span>
                </label>

                <select
                    id="branch_id"
                    name="branch_id"
                    class="form-select"
                    required
                >
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

                @error('branch_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Lengkap --}}
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

                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
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
                    Email ini akan digunakan sebagai akun login setelah aktivasi.
                </p>

                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nominal Simpanan Manasuka --}}
            <div>
                <label for="amount_saving_display" class="form-label">
                    Nominal Simpanan Manasuka
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3
                               text-sm font-semibold text-slate-500 dark:text-slate-400"
                    >
                        Rp
                    </span>

                    <input
                        id="amount_saving_display"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        class="form-control pl-10"
                        placeholder="0"
                        value=""
                        required
                    >
                </div>

                <input
                    id="amount_saving"
                    type="hidden"
                    name="amount_saving"
                    value="{{ old('amount_saving') }}"
                >

                @error('amount_saving')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Bergabung --}}
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

                @error('join_date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-7">
            <button
                type="submit"
                class="btn btn-primary w-full"
            >
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

    <script>
        (() => {
            const displayInput = document.getElementById('amount_saving_display');
            const rawInput = document.getElementById('amount_saving');
            const form = document.getElementById('member-registration-form');

            if (!displayInput || !rawInput || !form) {
                return;
            }

            const formatCurrency = (value) => {
                if (!value) {
                    return '';
                }

                return new Intl.NumberFormat('id-ID', {
                    maximumFractionDigits: 0
                }).format(Number(value));
            };

            const normalizeAmount = (value) => {
                const digits = String(value || '').replace(/[^\d]/g, '');

                if (!digits) {
                    return '';
                }

                return String(parseInt(digits, 10));
            };

            const syncAmount = () => {
                const raw = normalizeAmount(displayInput.value);

                rawInput.value = raw;
                displayInput.value = raw
                    ? formatCurrency(raw)
                    : '';
            };

            displayInput.addEventListener('keydown', (event) => {
                const allowedKeys = [
                    'Backspace',
                    'Delete',
                    'Tab',
                    'Escape',
                    'Enter',
                    'ArrowLeft',
                    'ArrowRight',
                    'ArrowUp',
                    'ArrowDown',
                    'Home',
                    'End'
                ];

                if (
                    allowedKeys.includes(event.key) ||
                    event.ctrlKey ||
                    event.metaKey
                ) {
                    return;
                }

                if (!/^\d$/.test(event.key)) {
                    event.preventDefault();
                }
            });

            displayInput.addEventListener('input', syncAmount);

            displayInput.addEventListener('paste', () => {
                setTimeout(syncAmount, 0);
            });

            if (rawInput.value) {
                const initialRaw = normalizeAmount(rawInput.value);

                rawInput.value = initialRaw;
                displayInput.value = initialRaw
                    ? formatCurrency(initialRaw)
                    : '';
            }

            form.addEventListener('submit', () => {
                syncAmount();
            });
        })();
    </script>

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
