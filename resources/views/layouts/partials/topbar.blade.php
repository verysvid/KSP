<header class="app-topbar">
    <div class="topbar-left">
        <button type="button"
                class="menu-toggle"
                @click="sidebarOpen = true"
                aria-label="Buka menu">
            <span></span><span></span><span></span>
        </button>

        <div class="breadcrumb-area">
            <span class="breadcrumb-muted">Koperasi</span>
            <span class="breadcrumb-separator">/</span>
            <strong>{{ isset($title) ? $title : 'Dashboard' }}</strong>
        </div>
    </div>

    <div class="topbar-right">
        <button type="button"
                class="topbar-icon-btn"
                @click="toggleDarkMode()"
                :title="darkMode ? 'Light Mode' : 'Dark Mode'"
                aria-label="Ganti tema">
            <span x-show="!darkMode">☾</span>
            <span x-show="darkMode" x-cloak>☀</span>
        </button>

        <div class="user-menu" x-data="{ open: false }">
            <button type="button"
                    class="user-menu-trigger"
                    @click="open = !open"
                    @keydown.escape.window="open = false">
                <div class="avatar avatar-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="user-menu-text">
                    <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                    <span>{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                </div>
                <span class="user-chevron">⌄</span>
            </button>

            <div x-show="open"
                 x-transition
                 @click.outside="open = false"
                 class="user-dropdown"
                 x-cloak>
                @if (Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <span>◎</span> Profile
                    </a>
                @endif

                @if (Route::has('profile.edit') && Route::has('logout'))
                    <div class="dropdown-divider"></div>
                @endif

                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-danger">
                            <span>↪</span> Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</header>
