<section>

    <header>
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">
            Ubah Password
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
            Gunakan password yang kuat untuk menjaga keamanan akun Anda.
        </p>
    </header>


    <form
        method="post"
        action="{{ route('password.update') }}"
        class="mt-6 space-y-5"
    >
        @csrf
        @method('put')


        {{-- Password Saat Ini --}}
        <div>
            <x-input-label
                for="update_password_current_password"
                value="Password Saat Ini"
            />

            <div
                x-data="{ showPassword: false }"
                class="relative mt-1"
            >
                <x-text-input
                    id="update_password_current_password"
                    name="current_password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    class="block w-full pr-12"
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    x-on:click="showPassword = !showPassword"
                    class="
                        absolute
                        inset-y-0
                        right-0
                        flex
                        items-center
                        justify-center
                        px-4
                        text-slate-400
                        transition
                        hover:text-slate-600
                        focus:outline-none
                        dark:text-slate-500
                        dark:hover:text-slate-300
                    "
                    x-bind:aria-label="
                        showPassword
                            ? 'Sembunyikan password'
                            : 'Tampilkan password'
                    "
                >

                    {{-- Mata terbuka --}}
                    <svg
                        x-show="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                    </svg>


                    {{-- Mata dicoret --}}
                    <svg
                        x-show="showPassword"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                        />
                    </svg>

                </button>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('current_password')"
                class="mt-2"
            />
        </div>


        {{-- Password Baru --}}
        <div>
            <x-input-label
                for="update_password_password"
                value="Password Baru"
            />

            <div
                x-data="{ showPassword: false }"
                class="relative mt-1"
            >
                <x-text-input
                    id="update_password_password"
                    name="password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    class="block w-full pr-12"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    x-on:click="showPassword = !showPassword"
                    class="
                        absolute
                        inset-y-0
                        right-0
                        flex
                        items-center
                        justify-center
                        px-4
                        text-slate-400
                        transition
                        hover:text-slate-600
                        focus:outline-none
                        dark:text-slate-500
                        dark:hover:text-slate-300
                    "
                    x-bind:aria-label="
                        showPassword
                            ? 'Sembunyikan password'
                            : 'Tampilkan password'
                    "
                >

                    {{-- Mata terbuka --}}
                    <svg
                        x-show="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                    </svg>


                    {{-- Mata dicoret --}}
                    <svg
                        x-show="showPassword"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                        />
                    </svg>

                </button>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('password')"
                class="mt-2"
            />
        </div>


        {{-- Konfirmasi Password Baru --}}
        <div>
            <x-input-label
                for="update_password_password_confirmation"
                value="Konfirmasi Password Baru"
            />

            <div
                x-data="{ showPassword: false }"
                class="relative mt-1"
            >
                <x-text-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    class="block w-full pr-12"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    x-on:click="showPassword = !showPassword"
                    class="
                        absolute
                        inset-y-0
                        right-0
                        flex
                        items-center
                        justify-center
                        px-4
                        text-slate-400
                        transition
                        hover:text-slate-600
                        focus:outline-none
                        dark:text-slate-500
                        dark:hover:text-slate-300
                    "
                    x-bind:aria-label="
                        showPassword
                            ? 'Sembunyikan password'
                            : 'Tampilkan password'
                    "
                >

                    {{-- Mata terbuka --}}
                    <svg
                        x-show="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                    </svg>


                    {{-- Mata dicoret --}}
                    <svg
                        x-show="showPassword"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                        />
                    </svg>

                </button>
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('password_confirmation')"
                class="mt-2"
            />
        </div>


        {{-- Action --}}
        <div class="flex flex-wrap items-center gap-4 pt-2">

            <x-primary-button>
                Ubah Password
            </x-primary-button>

            @if (session('status') === 'password-updated')

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
                    Password berhasil diperbarui.
                </p>

            @endif

        </div>

    </form>

</section>