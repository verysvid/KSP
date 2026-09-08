<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appSettings->abbreviation }}{{ $appSettings->title_2 ? ' - '.$appSettings->title_2 : '' }}</title>

    @if($appSettings->icon_url)
        <link rel="icon" href="{{ $appSettings->icon_url }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-6 bg-gray-100 dark:bg-gray-900">

        <div class="w-full text-center">
            <a href="{{ url('/') }}" class="inline-flex max-w-6xl flex-col items-center justify-center gap-3 sm:flex-row sm:items-start">
                @if($appSettings->logo_url)
                    <img src="{{ $appSettings->logo_url }}"
                         alt="{{ $appSettings->abbreviation }} Logo"
                         class="h-16 w-16 shrink-0 object-contain sm:h-20 sm:w-20">
                @endif

                <div class="text-center">
                    <h1 class="text-2xl font-extrabold text-slate-800 sm:text-3xl lg:text-4xl">
                        {{ $appSettings->title_1 }}
                    </h1>

                    @if($appSettings->title_2)
                        <p class="mt-2 text-lg font-medium text-slate-500 sm:text-xl">
                            {{ $appSettings->title_2 }}
                        </p>
                    @endif
                </div>
            </a>
        </div>

        <div
            class="w-full mt-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg
                {{ $attributes->has('wide')
                    ? 'sm:max-w-6xl xl:max-w-7xl px-4 sm:px-6 lg:px-8 py-6'
                    : 'sm:max-w-md px-6 py-4' }}"
        >
            {{ $slot }}
        </div>
    </div>
</body>
</html>
