@can('report.member-deductions.view')
    @if (Route::has('reports.member-deductions.index'))
        <a
            href="{{ route('reports.member-deductions.index') }}"
            class="nav-item {{ request()->routeIs('reports.member-deductions.*') ? 'active' : '' }}"
        >
            <span class="nav-icon">▦</span>
            <span>Daftar Potongan Anggota</span>
        </a>
    @endif
@endcan
