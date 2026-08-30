<aside
    class="app-sidebar"
    :class="{ 'sidebar-mobile-open': sidebarOpen }"
    @keydown.escape.window="sidebarOpen = false">

    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <div class="brand-logo">K</div>
            <div class="brand-copy">
                <strong>{{ config('app.name', 'Koperasi') }}</strong>
                <span>Management System</span>
            </div>
        </a>

        <button type="button"
                class="mobile-close-btn"
                @click="sidebarOpen = false"
                aria-label="Tutup menu">&times;</button>
    </div>

    <div class="sidebar-scroll">
        <div class="sidebar-section-title">MAIN MENU</div>
        <nav class="sidebar-nav">
            @can('dashboard.view')
                @if (Route::has('dashboard'))
                    <a href="{{ route('dashboard') }}"
                       class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">⌂</span>
                        <span>Dashboard</span>
                    </a>
                @endif
            @endcan
        </nav>

        <div class="sidebar-section-title">MASTER DATA</div>
        <nav class="sidebar-nav">
            @can('branch.view')
                @if (Route::has('branches.index'))
                    <a href="{{ route('branches.index') }}"
                       class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <span class="nav-icon">⌘</span>
                        <span>Cabang</span>
                    </a>
                @endif
            @endcan

            @can('member.view')
                @if (Route::has('members.index'))
                    <a href="{{ route('members.index') }}"
                       class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}">
                        <span class="nav-icon">♙</span>
                        <span>Anggota</span>
                    </a>
                @endif
            @endcan

            @can('saving-type.view')
                @if (Route::has('saving-types.index'))
                    <a href="{{ route('saving-types.index') }}"
                       class="nav-item {{ request()->routeIs('saving-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">▣</span>
                        <span>Jenis Simpanan</span>
                    </a>
                @endif
            @endcan

            @can('loan-type.view')
                @if (Route::has('loan-types.index'))
                    <a href="{{ route('loan-types.index') }}"
                       class="nav-item {{ request()->routeIs('loan-types.*') ? 'active' : '' }}">
                        <span class="nav-icon">▣</span>
                        <span>Jenis Pinjaman</span>
                    </a>
                @endif
            @endcan
        </nav>

        <div class="sidebar-section-title">TRANSAKSI</div>
        <nav class="sidebar-nav">
            @can('saving-transaction.view')
                @if (Route::has('saving-transactions.index'))
                    <a href="{{ route('saving-transactions.index') }}"
                       class="nav-item {{ request()->routeIs('saving-transactions.*') ? 'active' : '' }}">
                        <span class="nav-icon">⇄</span>
                        <span>Simpanan</span>
                    </a>
                @endif
            @endcan

			@can('loan.view')
				@if (Route::has('loans.index'))
					<a href="{{ route('loans.index') }}"
						class="nav-item {{ request()->routeIs('loans.*') ? 'active' : '' }}">
						<span class="nav-icon">▣</span>
						<span>Pengajuan Pinjaman</span>
					</a>
				@endif
			@endcan

			@can('loan.view')
				@if (Route::has('loan-payments.index'))
					<a
						href="{{ route('loan-payments.index') }}"
						class="nav-item {{ request()->routeIs('loan-payments.*') ? 'active' : '' }}"
					>
						<span class="nav-icon">▦</span>
						<span>Pembayaran Angsuran</span>
					</a>
				@endif
			@endcan

			@can('loan.view')
				@if (Route::has('loan-dashboard.index'))
					<a
						href="{{ route('loan-dashboard.index') }}"
						class="nav-item {{ request()->routeIs('loan-dashboard.*') ? 'active' : '' }}"
					>
						<span class="nav-icon">▥</span>
						<span>Dashboard Pinjaman</span>
					</a>
				@endif
			@endcan
        </nav>

        <div class="sidebar-section-title">AKUNTANSI</div>
        <nav class="sidebar-nav">
			@can('journal.view')
				@if (Route::has('journal-entries.index'))
					<a
						href="{{ route('journal-entries.index') }}"
						class="nav-item {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}"
					>
						<span class="nav-icon">▤</span>
						<span>Jurnal Umum</span>
					</a>
				@endif
			@endcan
        </nav>

        <div class="sidebar-section-title">LAPORAN</div>
		@can('loan.view')
			@if (Route::has('loan-reports.outstanding'))
				<a
					href="{{ route('loan-reports.outstanding') }}"
					class="nav-item {{ request()->routeIs('loan-reports.*') ? 'active' : '' }}"
				>
					<span class="nav-icon">▤</span>
					<span>Laporan Pinjaman</span>
				</a>
			@endif
		@endcan

        <div class="sidebar-section-title">ADMINISTRATION</div>
        <nav class="sidebar-nav">
            @can('user.view')
                @if (Route::has('users.index'))
                    <a href="{{ route('users.index') }}"
                       class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span class="nav-icon">♚</span>
                        <span>User</span>
                    </a>
                @endif
            @endcan

            @can('audit-log.view')
                @if (Route::has('audit-logs.index'))
                    <a href="{{ route('audit-logs.index') }}"
                       class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <span class="nav-icon">◎</span>
                        <span>Audit Log</span>
                    </a>
                @endif
            @endcan
        </nav>
    </div>

    <div class="sidebar-bottom">
        <div class="sidebar-user-mini">
            <div class="avatar avatar-sm">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                <span>{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
            </div>
        </div>
    </div>
</aside>
