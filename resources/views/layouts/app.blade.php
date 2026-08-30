<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="appLayout" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Koperasi') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="app-body">
<div class="app-shell">
    <div x-show="sidebarOpen" x-transition.opacity class="sidebar-overlay"
         @click="sidebarOpen = false" x-cloak></div>

    @include('layouts.partials.sidebar')

    <div class="app-main">
        @include('layouts.partials.topbar')

        <main class="app-content">
            @yield('content')
        </main>

        <footer class="app-footer">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Koperasi') }}</span>
            <span class="footer-separator">•</span>
            <span>Cooperative Management System</span>
        </footer>
    </div>
</div>

@if(session('success'))
<script>
window.addEventListener('load', () => {
    if (window.swalSuccess) window.swalSuccess(@js(session('success')));
});
</script>
@endif

@if(session('error'))
<script>
window.addEventListener('load', () => {
    if (window.swalError) window.swalError(@js(session('error')));
});
</script>
@endif

@stack('scripts')
</body>
</html>
