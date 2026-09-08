<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appSettings->abbreviation }}{{ $appSettings->title_2 ? ' - '.$appSettings->title_2 : '' }}</title>
    @if($appSettings->icon_url)
        <link rel="icon" href="{{ $appSettings->icon_url }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-blue-100 text-slate-800">
<main class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-7xl">
        <div class="mb-12 flex flex-col items-center justify-center gap-5 text-center lg:flex-row lg:items-start">
            @if($appSettings->logo_url)
                <img src="{{ $appSettings->logo_url }}"
                     alt="{{ $appSettings->abbreviation }} Logo"
                     class="h-24 w-24 shrink-0 object-contain lg:h-28 lg:w-28">
            @endif

            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                    {{ $appSettings->title_1 }}
                </h1>

                @if($appSettings->title_2)
                    <p class="mt-3 text-xl font-bold text-blue-600 sm:text-2xl lg:text-3xl">
                        {{ $appSettings->title_2 }}
                    </p>
                @endif

                @if($appSettings->description)
                    <p class="mx-auto mt-4 max-w-3xl text-lg font-medium leading-8 text-slate-600 sm:text-xl">
                        {{ $appSettings->description }}
                    </p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-slate-500">Total Anggota</p><p class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-2xl text-blue-600">♙</div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-slate-500">Total Simpanan</p><p class="mt-2 text-2xl font-bold text-slate-900">Rp 0</p></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-2xl text-emerald-600">▣</div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-slate-500">Total Pinjaman</p><p class="mt-2 text-2xl font-bold text-slate-900">Rp 0</p></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-2xl text-amber-600">$</div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-slate-500">Cabang Aktif</p><p class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-2xl text-violet-600">▥</div>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center justify-center gap-3 sm:flex-row">
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex min-w-[200px] items-center justify-center rounded-xl bg-blue-600 px-8 py-3.5 text-base font-bold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-xl">Masuk Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-w-[200px] items-center justify-center rounded-xl bg-blue-600 px-8 py-3.5 text-base font-bold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-xl">Login</a>
                @if(Route::has('member-registration.create'))
                    <a href="{{ route('member-registration.create') }}" class="inline-flex min-w-[200px] items-center justify-center rounded-xl border border-blue-600 bg-white px-8 py-3.5 text-base font-bold text-blue-600 shadow-lg transition hover:bg-blue-50 hover:shadow-xl">Registrasi Anggota</a>
                @endif
            @endauth
        </div>

        <footer class="mt-10 text-center text-sm text-slate-400">
            {{ $appSettings->copyright ?: '© '.date('Y').'. All rights reserved.' }}
        </footer>
    </div>
</main>
</body>
</html>
