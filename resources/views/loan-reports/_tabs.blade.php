<div class="mb-5 flex flex-wrap gap-2">
    <a href="{{ route('loan-reports.outstanding') }}"
       class="btn {{ request()->routeIs('loan-reports.outstanding') ? 'btn-primary' : 'btn-secondary' }}">
        Outstanding
    </a>

    <a href="{{ route('loan-reports.due') }}"
       class="btn {{ request()->routeIs('loan-reports.due') ? 'btn-primary' : 'btn-secondary' }}">
        Jatuh Tempo
    </a>

    <a href="{{ route('loan-reports.overdue') }}"
       class="btn {{ request()->routeIs('loan-reports.overdue') ? 'btn-primary' : 'btn-secondary' }}">
        Tunggakan
    </a>

    <a href="{{ route('loan-reports.payments') }}"
       class="btn {{ request()->routeIs('loan-reports.payments') ? 'btn-primary' : 'btn-secondary' }}">
        Riwayat Pembayaran
    </a>
</div>
