@role('Anggota')
    @can('member-loan-application.view')
        @if(Route::has('member-loan-applications.index'))
            <div class="sidebar-section-title">PINJAMAN</div>

            <a href="{{ route('member-loan-applications.index') }}"
               class="nav-item {{ request()->routeIs('member-loan-applications.*') ? 'active' : '' }}">
                <span class="nav-icon">▣</span>
                <span>Pengajuan Pinjaman Saya</span>
            </a>
        @endif
    @endcan
@endrole
