<x-app-layout>
    <x-slot name="title">Pengajuan Pinjaman</x-slot>

    <x-page-header
        title="Pengajuan Pinjaman"
        description="Kelola pengajuan pinjaman anggota koperasi.">

        @can('loan.create')
            <x-slot name="actions">
                <a href="{{ route('loans.create') }}"
                   class="btn btn-primary">
                    + Tambah Pengajuan
                </a>
            </x-slot>
        @endcan
    </x-page-header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Pengajuan</span>
                <div class="stat-icon">▣</div>
            </div>
            <div class="stat-value">{{ $totalLoans }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Draft</span>
                <div class="stat-icon">✎</div>
            </div>
            <div class="stat-value">{{ $draftLoans }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Menunggu Approval</span>
                <div class="stat-icon">!</div>
            </div>
            <div class="stat-value">{{ $submittedLoans }}</div>
        </div>
    </div>

    <x-card>
        <form
            method="GET"
            action="{{ route('loans.index') }}"
            class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[minmax(280px,1fr)_180px_180px_180px_auto]">

            <input
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari no. pinjaman, anggota, jenis..."
                class="form-control">

            <select
                name="loan_type_id"
                class="form-select">
                <option value="">Semua Jenis</option>

                @foreach($loanTypes as $loanType)
                    <option
                        value="{{ $loanType->id }}"
                        @selected((string) request('loan_type_id') === (string) $loanType->id)
                    >
                        {{ $loanType->code }} - {{ $loanType->name }}
                    </option>
                @endforeach
            </select>

            <select
                name="status"
                class="form-select">
                <option value="">Semua Status</option>
                <option value="DRAFT" @selected(request('status') === 'DRAFT')>Draft</option>
                <option value="SUBMITTED" @selected(request('status') === 'SUBMITTED')>Submitted</option>
                <option value="APPROVED" @selected(request('status') === 'APPROVED')>Approved</option>
                <option value="REJECTED" @selected(request('status') === 'REJECTED')>Rejected</option>
                <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>Active</option>
                <option value="PAID_OFF" @selected(request('status') === 'PAID_OFF')>Paid Off</option>
                <option value="CANCELLED" @selected(request('status') === 'CANCELLED')>Cancelled</option>
            </select>

            @if(auth()->user()?->hasRole('SuperAdmin'))
                <select
                    name="branch_id"
                    class="form-select">
                    <option value="">Semua Cabang</option>

                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected((string) request('branch_id') === (string) $branch->id)
                        >
                            {{ $branch->code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <div class="hidden md:block"></div>
            @endif

            <div class="flex gap-2">
                <button type="submit"
                        class="btn btn-primary flex-1">
                    Cari
                </button>

                <a href="{{ route('loans.index') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </form>

        {{-- Desktop --}}
        <div class="hidden md:block">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No. Pinjaman</th>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Tenor</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>
                                <span class="table-primary text-indigo-600 dark:text-indigo-400">
                                    {{ $loan->loan_no }}
                                </span>

                                <span class="table-secondary">
                                    {{ $loan->application_date?->format('d/m/Y') ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $loan->member->name ?? '-' }}
                                </span>

                                @if(auth()->user()?->hasRole('SuperAdmin'))
                                    <span class="table-secondary">
                                        {{ $loan->branch->code ?? '-' }} - {{ $loan->branch->name ?? '-' }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="table-primary">
                                    {{ $loan->loanType->name ?? '-' }}
                                </span>

                                <span class="table-secondary">
                                    {{ $loan->interest_type }}
                                    ·
                                    {{ number_format((float) $loan->interest_rate, 4, ',', '.') }}%
                                </span>
                            </td>

                            <td>
                                Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ $loan->tenor_months }} bulan
                            </td>

                            <td>
                                <x-status-badge :status="$loan->status" />
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('loans.show', $loan) }}"
                                       class="btn btn-secondary">
                                        Detail
                                    </a>

                                    @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.edit'))
                                        <a href="{{ route('loans.edit', $loan) }}"
                                           class="btn btn-secondary">
                                            Edit
                                        </a>
                                    @endif

                                    @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.submit'))
                                        <form
                                            method="POST"
                                            action="{{ route('loans.submit', $loan) }}"
                                            class="loan-submit-form"
                                            data-loan-no="{{ $loan->loan_no }}"
                                            data-member="{{ $loan->member->name ?? '-' }}">
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-primary">
                                                Submit
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="empty-state">
                                Belum ada pengajuan pinjaman.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @forelse($loans as $loan)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                            dark:border-slate-700 dark:bg-slate-800/60">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $loan->loan_no }}
                            </div>

                            <div class="mt-1 truncate font-semibold text-slate-900 dark:text-white">
                                {{ $loan->member->name ?? '-' }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $loan->loanType->name ?? '-' }}
                            </div>
                        </div>

                        <x-status-badge :status="$loan->status" />
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-slate-500 dark:text-slate-400">Nominal</div>
                            <div class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                                Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-slate-500 dark:text-slate-400">Tenor</div>
                            <div class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                                {{ $loan->tenor_months }} bulan
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('loans.show', $loan) }}"
                           class="btn btn-secondary">
                            Detail
                        </a>

                        @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.edit'))
                            <a href="{{ route('loans.edit', $loan) }}"
                               class="btn btn-primary">
                                Edit
                            </a>
                        @endif
                    </div>

                    @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.submit'))
                        <form
                            method="POST"
                            action="{{ route('loans.submit', $loan) }}"
                            class="loan-submit-form mt-2"
                            data-loan-no="{{ $loan->loan_no }}"
                            data-member="{{ $loan->member->name ?? '-' }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-primary w-full">
                                Submit Pengajuan
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <x-empty-state
                    title="Belum ada pengajuan pinjaman"
                    description="Silakan tambahkan pengajuan pinjaman pertama." />
            @endforelse
        </div>

        @if($loans->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-5
                        dark:border-slate-800">
                {{ $loans->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script>
            document.querySelectorAll('.loan-submit-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Submit Pengajuan Pinjaman?',
                            html:
                                `Pengajuan <strong>${escapeLoanHtml(form.dataset.loanNo)}</strong>` +
                                ` atas nama <strong>${escapeLoanHtml(form.dataset.member)}</strong>` +
                                ` akan dikirim untuk proses approval.`,
                            confirmButtonText: 'Ya, Submit',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            function escapeLoanHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value || '');
                return div.innerHTML;
            }
        </script>
    @endpush
</x-app-layout>
