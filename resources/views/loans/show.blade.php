<x-app-layout>
    <x-slot name="title">Detail Pengajuan Pinjaman</x-slot>

    <x-page-header
        title="Detail Pengajuan Pinjaman"
        description="{{ $loan->loan_no }} - {{ $loan->member->name ?? '-' }}">

        <x-slot name="actions">
            <a href="{{ route('loans.index') }}"
               class="btn btn-secondary">
                Kembali
            </a>

            @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.edit'))
                <a href="{{ route('loans.edit', $loan) }}"
                   class="btn btn-secondary">
                    Edit Pengajuan
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

                    <button type="submit"
                            class="btn btn-primary">
                        Submit Pengajuan
                    </button>
                </form>
            @endif

			@if(in_array($loan->status, ['ACTIVE', 'PAID_OFF'], true))
				<form method="POST" action="{{ route('loans.overdue.refresh') }}">
					@csrf
					@method('PATCH')
					<button type="submit" class="btn btn-secondary">Refresh Overdue</button>
				</form>
			@endif

            @if($loan->status === 'SUBMITTED' && auth()->user()?->can('loan.approve'))
                <form
                    method="POST"
                    action="{{ route('loans.approve', $loan) }}"
                    class="loan-approve-form"
                    data-loan-no="{{ $loan->loan_no }}"
                    data-member="{{ $loan->member->name ?? '-' }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="btn btn-primary">
                        Approve
                    </button>
                </form>
            @endif

			@if($loan->status === 'APPROVED' && auth()->user()?->can('loan.disburse'))
				<a href="{{ route('loans.disbursements.create', $loan) }}"
				   class="btn btn-primary">
					Proses Pencairan
				</a>
			@endif
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center
                            rounded-2xl bg-indigo-100 text-2xl font-extrabold
                            text-indigo-700
                            dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($loan->loanType->code ?? 'LN', 0, 2)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $loan->member->name ?? '-' }}
                </h2>

                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ $loan->loan_no }}
                </div>

                <div class="mt-3">
                    <x-status-badge :status="$loan->status" />
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 px-4 py-4
                            dark:bg-slate-800/60">
                    <div class="text-xs font-medium uppercase tracking-wide
                                text-slate-500 dark:text-slate-400">
                        Nominal Pinjaman
                    </div>

                    <div class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Informasi Pengajuan"
            description="Detail transaksi pengajuan pinjaman.">

            <div class="info-list">
                <div class="info-row">
                    <span>No. Pinjaman</span>
                    <strong>{{ $loan->loan_no }}</strong>
                </div>

                <div class="info-row">
                    <span>Tanggal Pengajuan</span>
                    <strong>{{ $loan->application_date?->format('d/m/Y') ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Cabang</span>
                    <strong>
                        {{ $loan->branch->code ?? '-' }}
                        -
                        {{ $loan->branch->name ?? '-' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Anggota</span>
                    <strong>{{ $loan->member->name ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Jenis Pinjaman</span>
                    <strong>
                        {{ $loan->loanType->code ?? '-' }}
                        -
                        {{ $loan->loanType->name ?? '-' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Nominal Pinjaman</span>
                    <strong>
                        Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Metode Bunga</span>
                    <strong>{{ $loan->interest_type }}</strong>
                </div>

                <div class="info-row">
                    <span>Bunga</span>
                    <strong>
						{{ str_replace('.', ',', rtrim(rtrim(number_format((float) $loan->interest_rate, 4, '.', ''), '0'), '.')) }}%
                    </strong>
                </div>

                <div class="info-row">
                    <span>Tenor</span>
                    <strong>{{ $loan->tenor_months }} bulan</strong>
                </div>

                <div class="info-row">
                    <span>Tanggal Jatuh Tempo Bulanan</span>
                    <strong>Tanggal {{ $loan->due_day }}</strong>
                </div>

                <div class="info-row">
                    <span>Status</span>
                    <strong>
                        <x-status-badge :status="$loan->status" />
                    </strong>
                </div>

                <div class="info-row">
                    <span>Catatan</span>
                    <strong class="max-w-md">
                        {{ $loan->notes ?: '-' }}
                    </strong>
                </div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card
            title="Proses Pengajuan"
            description="Riwayat proses pengajuan dan keputusan pinjaman.">

            <div class="info-list">
                <div class="info-row">
                    <span>Dibuat Oleh</span>
                    <strong>{{ $loan->createdBy->name ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Dibuat Pada</span>
                    <strong>{{ $loan->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Disubmit Oleh</span>
                    <strong>{{ $loan->submittedBy->name ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Waktu Submit</span>
                    <strong>{{ $loan->submitted_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                @if($loan->approved_at)
                    <div class="info-row">
                        <span>Disetujui Oleh</span>
                        <strong>{{ $loan->approvedBy->name ?? '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Waktu Approval</span>
                        <strong>{{ $loan->approved_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif

                @if($loan->rejected_at)
                    <div class="info-row">
                        <span>Ditolak Oleh</span>
                        <strong>{{ $loan->rejectedBy->name ?? '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Waktu Penolakan</span>
                        <strong>{{ $loan->rejected_at->format('d/m/Y H:i') }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Alasan Penolakan</span>
                        <strong class="max-w-md text-red-600 dark:text-red-400">
                            {{ $loan->rejection_reason ?: '-' }}
                        </strong>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card
            title="Nilai Pinjaman"
            description="Nilai transaksi sebelum proses pencairan dan pembentukan jadwal.">

            <div class="info-list">
                <div class="info-row">
                    <span>Total Pokok</span>
                    <strong>
                        Rp {{ number_format((float) $loan->total_principal, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Total Bunga</span>
                    <strong>
                        Rp {{ number_format((float) $loan->total_interest, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Total Angsuran</span>
                    <strong>
                        Rp {{ number_format((float) $loan->total_installment, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Sisa Pokok</span>
                    <strong>
                        Rp {{ number_format((float) $loan->outstanding_principal, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            @if(in_array($loan->status, ['DRAFT', 'SUBMITTED', 'APPROVED'], true))
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                            text-sm text-slate-600
                            dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                    Jadwal angsuran belum dibentuk. Jadwal akan dibuat setelah proses pencairan pinjaman.
                </div>
            @endif
        </x-card>
    </div>

    @if($loan->status === 'SUBMITTED' && auth()->user()?->can('loan.reject'))
        <div class="mt-6">
            <x-card
                title="Tolak Pengajuan"
                description="Gunakan bagian ini hanya jika pengajuan pinjaman tidak disetujui.">

                <form
                    method="POST"
                    action="{{ route('loans.reject', $loan) }}"
                    class="loan-reject-form"
                    data-loan-no="{{ $loan->loan_no }}">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="rejection_reason" class="form-label">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="rejection_reason"
                            name="rejection_reason"
                            rows="4"
                            required
                            maxlength="2000"
                            class="form-textarea"
                            placeholder="Jelaskan alasan pengajuan pinjaman ditolak"
                        >{{ old('rejection_reason') }}</textarea>

                        @error('rejection_reason')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5 flex justify-end border-t border-slate-200 pt-5
                                dark:border-slate-800">
                        <button type="submit"
                                class="btn btn-danger">
                            Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    @endif

    @if($loan->status === 'APPROVED')
        <div class="mt-6">
            <x-card
                title="Tahap Berikutnya"
                description="Pengajuan telah disetujui dan siap diproses untuk pencairan.">

                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-4
                            text-sm text-slate-600
                            dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                    Status saat ini <strong>APPROVED</strong>.
                    Belum ada jurnal dan belum ada jadwal angsuran pada tahap approval.
                    Keduanya akan diproses pada tahap pencairan berikutnya.
                </div>
            </x-card>
        </div>
    @endif

    @if($loan->status === 'DRAFT' && auth()->user()?->can('loan.delete'))
        <div class="mt-6 flex justify-end">
            <form
                method="POST"
                action="{{ route('loans.destroy', $loan) }}"
                class="loan-delete-form"
                data-loan-no="{{ $loan->loan_no }}">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="btn btn-danger">
                    Hapus Draft
                </button>
            </form>
        </div>
    @endif

	@if($loan->status === 'ACTIVE' || $loan->status === 'PAID_OFF')
		<div class="mt-6">
			<x-card
				title="Jadwal Angsuran"
				description="Jadwal angsuran terbentuk setelah pencairan pinjaman.">

				<div class="hidden md:block">
					<div class="table-wrapper">
						<table class="data-table">
							<thead>
							<tr>
								<th>Angsuran</th>
								<th>Jatuh Tempo</th>
								<th>Pokok</th>
								<th>Bunga</th>
								<th>Total</th>
								<th>Sisa Pokok</th>
								<th>Status</th>
								<th>Keterlambatan</th>
								<th class="text-right">Action</th>
							</tr>
							</thead>
							<tbody>
							@forelse($loan->installments as $installment)
								<tr>
									<td>#{{ $installment->installment_no }}</td>
									<td>{{ $installment->due_date?->format('d/m/Y') }}</td>
									<td>Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}</td>
									<td>Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}</td>
									<td>
										<strong>
											Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}
										</strong>
									</td>
									<td>Rp {{ number_format((float) $installment->ending_principal, 0, ',', '.') }}</td>
									<td>
										<x-status-badge :status="$installment->status" />
									</td>
									<td>
									@if($installment->status === 'PAID')
										@if($installment->days_overdue > 0)
											<span class="text-sm font-semibold text-red-600 dark:text-red-400">Terlambat {{ $installment->days_overdue }} hari</span>
										@else
											<span class="text-sm text-slate-500 dark:text-slate-400">Tepat waktu</span>
										@endif
									@elseif($installment->is_overdue)
										<div class="text-sm font-semibold text-red-600 dark:text-red-400">OVERDUE</div>
										<div class="text-xs text-slate-500 dark:text-slate-400">{{ $installment->days_overdue }} hari</div>
										<div class="mt-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Denda: Rp {{ number_format((float)$installment->penalty_amount,0,',','.') }}</div>
									@else
										<span class="text-sm text-slate-500 dark:text-slate-400">Belum jatuh tempo</span>
									@endif
									</td>
									<td>
										<div class="flex justify-end">
											@if(
												$loan->status === 'ACTIVE'
												&& $installment->status !== 'PAID'
												&& auth()->user()?->can('loan.pay')
											)
												<a
													href="{{ route('loans.installments.payments.create', [$loan, $installment]) }}"
													class="btn btn-primary">
													Bayar
												</a>
											@else
												<span class="text-sm text-slate-400">-</span>
											@endif
										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="9" class="empty-state">
										Jadwal angsuran belum tersedia.
									</td>
								</tr>
							@endforelse
							</tbody>
						</table>
					</div>
				</div>

				<div class="space-y-3 md:hidden">
					@foreach($loan->installments as $installment)
						<div class="rounded-xl border border-slate-200 bg-slate-50 p-4
									dark:border-slate-700 dark:bg-slate-800/60">
							<div class="flex items-start justify-between gap-3">
								<div>
									<div class="font-semibold text-slate-900 dark:text-white">
										Angsuran #{{ $installment->installment_no }}
									</div>
									<div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
										Jatuh tempo {{ $installment->due_date?->format('d/m/Y') }}
									</div>
								</div>
								<x-status-badge :status="$installment->status" />
							</div>

							<div class="mt-3 grid grid-cols-2 gap-3 text-sm">
								<div>
									<span class="text-slate-500 dark:text-slate-400">Pokok</span>
									<div class="font-semibold">
										Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}
									</div>
								</div>
								<div>
									<span class="text-slate-500 dark:text-slate-400">Bunga</span>
									<div class="font-semibold">
										Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}
									</div>
								</div>
								<div class="col-span-2">
									<span class="text-slate-500 dark:text-slate-400">Total Angsuran</span>
									<div class="font-bold text-slate-900 dark:text-white">
										Rp {{ number_format((float) $installment->installment_amount, 0, ',', '.') }}
									</div>
								</div>
								@if(
									$loan->status === 'ACTIVE'
									&& $installment->status !== 'PAID'
									&& auth()->user()?->can('loan.pay')
								)
									<div class="mt-4">
										<a
											href="{{ route('loans.installments.payments.create', [$loan, $installment]) }}"
											class="btn btn-primary w-full">
											Bayar Angsuran
										</a>
									</div>
								@endif

							</div>
						</div>
					@endforeach
				</div>
			</x-card>
		</div>
	@endif

	@if($loan->payments->isNotEmpty())
		<div class="mt-6">
			<x-card
				title="Riwayat Pembayaran"
				description="Pembayaran angsuran yang sudah diterima.">

				<div class="hidden md:block">
					<div class="table-wrapper">
						<table class="data-table">
							<thead>
							<tr>
								<th>No. Pembayaran</th>
								<th>Tanggal</th>
								<th>Angsuran</th>
								<th>Pokok</th>
								<th>Bunga</th>
								<th>Denda</th>
								<th>Total</th>
								<th>Kas / Bank</th>
							</tr>
							</thead>

							<tbody>
							@foreach($loan->payments as $payment)
								<tr>
									<td>
										<span class="table-primary text-indigo-600 dark:text-indigo-400">
											{{ $payment->payment_no }}
										</span>
									</td>

									<td>{{ $payment->payment_date?->format('d/m/Y') }}</td>

									<td>
										#{{ $payment->installment->installment_no ?? '-' }}
									</td>

									<td>
										Rp {{ number_format((float) $payment->principal_amount, 0, ',', '.') }}
									</td>

									<td>
										Rp {{ number_format((float) $payment->interest_amount, 0, ',', '.') }}
									</td>

									<td>
										Rp {{ number_format((float) $payment->penalty_amount, 0, ',', '.') }}
									</td>

									<td>
										<strong>
											Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
										</strong>
									</td>

									<td>
										{{ $payment->cashAccount->code ?? '-' }}
										-
										{{ $payment->cashAccount->name ?? '-' }}
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
				</div>

				<div class="space-y-3 md:hidden">
					@foreach($loan->payments as $payment)
						<div class="rounded-xl border border-slate-200 bg-slate-50 p-4
									dark:border-slate-700 dark:bg-slate-800/60">

							<div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
								{{ $payment->payment_no }}
							</div>

							<div class="mt-1 font-semibold text-slate-900 dark:text-white">
								Angsuran #{{ $payment->installment->installment_no ?? '-' }}
							</div>

							<div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
								{{ $payment->payment_date?->format('d/m/Y') }}
							</div>

							<div class="mt-3 text-lg font-bold text-slate-900 dark:text-white">
								Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}
							</div>
						</div>
					@endforeach
				</div>
			</x-card>
		</div>
	@endif

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

            document.querySelectorAll('.loan-approve-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Approve Pengajuan Pinjaman?',
                            html:
                                `Pengajuan <strong>${escapeLoanHtml(form.dataset.loanNo)}</strong>` +
                                ` atas nama <strong>${escapeLoanHtml(form.dataset.member)}</strong>` +
                                ` akan disetujui.`,
                            confirmButtonText: 'Ya, Approve',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            document.querySelectorAll('.loan-reject-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'warning',
                            title: 'Tolak Pengajuan Pinjaman?',
                            text: `Pengajuan ${form.dataset.loanNo} akan ditolak.`,
                            confirmButtonText: 'Ya, Tolak',
                            confirmButtonColor: '#dc2626',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            document.querySelectorAll('.loan-delete-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'warning',
                            title: 'Hapus Draft Pengajuan?',
                            text: `Draft ${form.dataset.loanNo} akan dihapus permanen.`,
                            confirmButtonText: 'Ya, Hapus',
                            confirmButtonColor: '#dc2626',
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
