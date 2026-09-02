<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
         {{ config('app.name') }} - {{ config('app.corporate_name') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-blue-100 text-slate-800">

    <main
        class="
            min-h-screen
            flex
            items-center
            justify-center
            px-4
            py-8
            sm:px-6
            lg:px-8
        "
    >

        <div class="w-full max-w-7xl">

            {{-- Header --}}
			<div class="mb-14 text-center">

				<h1
					class="
						text-3xl
						font-extrabold
						tracking-tight
						text-slate-900
						sm:text-4xl
						lg:text-5xl
					"
				>
					{{ config('app.alt_name') }}
				</h1>

				<p
					class="
						mt-4
						text-xl
						font-bold
						text-blue-600
						sm:text-2xl
						lg:text-3xl
					"
				>
					{{ config('app.corporate_name') }}
				</p>

				<p
					class="
						mx-auto
						mt-5
						max-w-3xl
						text-lg
						font-medium
						leading-8
						text-slate-600
						sm:text-xl
					"
				>
					Sistem Informasi Pengelolaan Koperasi Simpan Pinjam
				</p>

			</div>

            {{-- Dashboard Cards --}}
            <div
                class="
                    grid
                    grid-cols-1
                    gap-5
                    sm:grid-cols-2
                    lg:grid-cols-4
                "
            >

                {{-- Card Anggota --}}
                <div
                    class="
                        rounded-2xl
                        bg-white
                        p-6
                        shadow-sm
                        ring-1
                        ring-slate-200
                        transition
                        duration-200
                        hover:-translate-y-1
                        hover:shadow-md
                    "
                >
                    <div class="flex items-center justify-between">

                        <div>
                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Total Anggota
                            </p>

                            <p
                                class="
                                    mt-2
                                    text-3xl
                                    font-bold
                                    text-slate-900
                                "
                            >
                                0
                            </p>
                        </div>

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-blue-50
                                text-blue-600
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-7 w-7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="
                                        M18 18.72
                                        a9.094 9.094 0 0 0 3.741-.479
                                        3 3 0 0 0-4.682-2.72
                                        m.94 3.198.001.031
                                        c0 .225-.012.447-.037.666
                                        A11.944 11.944 0 0 1 12 21
                                        c-2.17 0-4.203-.576-5.963-1.584
                                        A6.062 6.062 0 0 1 6 18.719
                                        m12 0
                                        a5.971 5.971 0 0 0-.941-3.197
                                        m0 0
                                        A5.995 5.995 0 0 0 12 12.75
                                        a5.995 5.995 0 0 0-5.058 2.772
                                        m0 0
                                        a3 3 0 0 0-4.681 2.72
                                        8.986 8.986 0 0 0 3.74.477
                                        m.94-3.197
                                        a5.971 5.971 0 0 0-.94 3.197
                                        M15 6.75
                                        a3 3 0 1 1-6 0
                                        3 3 0 0 1 6 0
                                        Z
                                    "
                                />
                            </svg>
                        </div>

                    </div>
                </div>


                {{-- Card Simpanan --}}
                <div
                    class="
                        rounded-2xl
                        bg-white
                        p-6
                        shadow-sm
                        ring-1
                        ring-slate-200
                        transition
                        duration-200
                        hover:-translate-y-1
                        hover:shadow-md
                    "
                >
                    <div class="flex items-center justify-between">

                        <div>
                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Total Simpanan
                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                    text-slate-900
                                "
                            >
                                Rp 0
                            </p>
                        </div>

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-emerald-50
                                text-emerald-600
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-7 w-7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="
                                        M2.25 18.75
                                        a60.07 60.07 0 0 1 15.797 2.101
                                        c.727.198 1.453-.342 1.453-1.096
                                        V18.75
                                        M3.75 4.5
                                        v.75
                                        A.75.75 0 0 1 3 6
                                        h-.75
                                        m0 0
                                        v-.375
                                        c0-.621.504-1.125 1.125-1.125
                                        H20.25
                                        M2.25 6
                                        v9
                                        m18-10.5
                                        v.75
                                        c0 .414.336.75.75.75
                                        h.75
                                        m-1.5-1.5
                                        h.375
                                        c.621 0 1.125.504 1.125 1.125
                                        V15.75
                                        M18 18.75
                                        h1.5
                                        a2.25 2.25 0 0 0 2.25-2.25
                                        v-.75
                                        M18 18.75
                                        v-1.5
                                        c0-.621.504-1.125 1.125-1.125
                                        h2.625
                                        M15.75 9
                                        a3.75 3.75 0 1 1-7.5 0
                                        3.75 3.75 0 0 1 7.5 0
                                        Z
                                    "
                                />
                            </svg>
                        </div>

                    </div>
                </div>


                {{-- Card Pinjaman --}}
                <div
                    class="
                        rounded-2xl
                        bg-white
                        p-6
                        shadow-sm
                        ring-1
                        ring-slate-200
                        transition
                        duration-200
                        hover:-translate-y-1
                        hover:shadow-md
                    "
                >
                    <div class="flex items-center justify-between">

                        <div>
                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Total Pinjaman
                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                    text-slate-900
                                "
                            >
                                Rp 0
                            </p>
                        </div>

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-amber-50
                                text-amber-600
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-7 w-7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="
                                        M12 6
                                        v12
                                        m3-9.75
                                        C15 7.007 13.657 6 12 6
                                        S9 7.007 9 8.25
                                        c0 1.243 1.343 2.25 3 2.25
                                        s3 1.007 3 2.25
                                        S13.657 15 12 15
                                        s-3-1.007-3-2.25
                                        M21 12
                                        a9 9 0 1 1-18 0
                                        9 9 0 0 1 18 0
                                        Z
                                    "
                                />
                            </svg>
                        </div>

                    </div>
                </div>


                {{-- Card Cabang --}}
                <div
                    class="
                        rounded-2xl
                        bg-white
                        p-6
                        shadow-sm
                        ring-1
                        ring-slate-200
                        transition
                        duration-200
                        hover:-translate-y-1
                        hover:shadow-md
                    "
                >
                    <div class="flex items-center justify-between">

                        <div>
                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Cabang Aktif
                            </p>

                            <p
                                class="
                                    mt-2
                                    text-3xl
                                    font-bold
                                    text-slate-900
                                "
                            >
                                0
                            </p>
                        </div>

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-violet-50
                                text-violet-600
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-7 w-7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="
                                        M3.75 21
                                        h16.5
                                        M4.5 3
                                        h15
                                        M5.25 3
                                        v18
                                        m13.5-18
                                        v18
                                        M9 6.75
                                        h1.5
                                        M9 10.5
                                        h1.5
                                        m3-3.75
                                        H15
                                        m-1.5 3.75
                                        H15
                                        M9 21
                                        v-3.375
                                        c0-.621.504-1.125 1.125-1.125
                                        h3.75
                                        c.621 0 1.125.504 1.125 1.125
                                        V21
                                    "
                                />
                            </svg>
                        </div>

                    </div>
                </div>

            </div>


            {{-- Login --}}
			<div class="mt-12 flex justify-center">

				@auth

					<a
						href="{{ url('/dashboard') }}"
						class="
							inline-flex
							min-w-[180px]
							items-center
							justify-center
							rounded-xl
							bg-blue-600
							px-8
							py-3.5
							text-base
							font-bold
							text-white
							shadow-lg
							transition
							duration-200
							hover:bg-blue-700
							hover:shadow-xl
							focus:outline-none
							focus:ring-4
							focus:ring-blue-200
						"
					>
						Masuk Dashboard
					</a>

				@else

					<a
						href="{{ route('login') }}"
						class="
							inline-flex
							min-w-[180px]
							items-center
							justify-center
							rounded-xl
							bg-blue-600
							px-8
							py-3.5
							text-base
							font-bold
							text-white
							shadow-lg
							transition
							duration-200
							hover:bg-blue-700
							hover:shadow-xl
							focus:outline-none
							focus:ring-4
							focus:ring-blue-200
						"
					>
						Login
					</a>

				@endauth

			</div>

            {{-- Footer --}}
            <div
                class="
                    mt-10
                    text-center
                    text-xs
                    text-slate-400
                    sm:text-sm
                "
            >
                &copy; {{ date('Y') }}
                {{ config('app.cooperative_name') }}.
                All rights reserved.
            </div>

        </div>

    </main>

</body>
</html>