<section>

    <header>
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">
            Informasi Profil
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
            Perbarui nama dan alamat email akun Anda.
        </p>
    </header>


    <form
        id="send-verification"
        method="post"
        action="{{ route('verification.send') }}"
    >
        @csrf
    </form>


    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-5"
    >
        @csrf
        @method('patch')


        {{-- Nama --}}
        <div>
            <x-input-label
                for="name"
                value="Nama"
            />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('name')"
            />
        </div>


        {{-- Email --}}
        <div>
            <x-input-label
                for="email"
                value="Email"
            />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')"
            />


            @if (
                $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
                && ! $user->hasVerifiedEmail()
            )

                <div class="mt-3">

                    <p class="text-sm text-slate-600 dark:text-slate-300">

                        Alamat email Anda belum diverifikasi.

                        <button
                            form="send-verification"
                            class="
                                font-medium
                                text-blue-600
                                underline
                                transition
                                hover:text-blue-700
                                focus:outline-none
                            "
                        >
                            Kirim ulang email verifikasi
                        </button>

                    </p>


                    @if (session('status') === 'verification-link-sent')

                        <p
                            class="
                                mt-2
                                text-sm
                                font-medium
                                text-emerald-600
                                dark:text-emerald-400
                            "
                        >
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </p>

                    @endif

                </div>

            @endif

        </div>


        {{-- Action --}}
        <div class="flex flex-wrap items-center gap-4 pt-2">

            <x-primary-button>
                Simpan Perubahan
            </x-primary-button>


            @if (session('status') === 'profile-updated')

                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="
                        text-sm
                        font-medium
                        text-emerald-600
                        dark:text-emerald-400
                    "
                >
                    Profil berhasil diperbarui.
                </p>

            @endif

        </div>

    </form>

</section>