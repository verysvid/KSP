<x-app-layout>
    <x-slot name="title">Detail Transaksi Simpanan</x-slot>

    <x-page-header
        title="Detail Transaksi Simpanan"
        description="{{ $savingTransaction->trx_no }}">

        <x-slot name="actions">
            <a href="{{ route('saving-transactions.index') }}"
               class="btn btn-secondary">Kembali</a>

            @can('approve', $savingTransaction)
                <form method="POST"
                      action="{{ route('saving-transactions.approve', $savingTransaction) }}"
                      class="approve-saving-transaction-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary">Approve</button>
                </form>
            @endcan

            @can('reject', $savingTransaction)
                <button type="button"
                        class="btn btn-danger"
                        id="rejectTransactionBtn">
                    Reject
                </button>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl
                            {{ $savingTransaction->credit > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">
                    {{ $savingTransaction->credit > 0 ? '↑' : '↓' }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $savingTransaction->mutation_type }}
                </h2>

                <div class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">
                    Rp {{ number_format($savingTransaction->amount, 0, ',', '.') }}
                </div>

                <div class="mt-3">
                    <x-status-badge :status="$savingTransaction->status" />
                </div>
            </div>
        </x-card>

        <x-card class="lg:col-span-2"
                title="Informasi Transaksi"
                description="Detail transaksi simpanan anggota.">

            <div class="info-list">
                <div class="info-row"><span>No. Transaksi</span><strong>{{ $savingTransaction->trx_no }}</strong></div>
                <div class="info-row"><span>Tanggal</span><strong>{{ $savingTransaction->transaction_date?->format('d/m/Y') }}</strong></div>
                <div class="info-row"><span>Periode</span><strong>{{ $savingTransaction->period }}</strong></div>
                <div class="info-row"><span>Cabang</span><strong>{{ $savingTransaction->branch?->name ?? '-' }}</strong></div>
                <div class="info-row"><span>Anggota</span><strong>{{ $savingTransaction->member?->member_number }} - {{ $savingTransaction->member?->name }}</strong></div>
                <div class="info-row"><span>Jenis Simpanan</span><strong>{{ $savingTransaction->savingType?->name ?? '-' }}</strong></div>
                <div class="info-row"><span>Saldo Approved Saat Ini</span><strong>Rp {{ number_format($approvedBalance, 0, ',', '.') }}</strong></div>
                <div class="info-row"><span>Approver</span><strong>{{ $savingTransaction->approver?->name ?? '-' }}</strong></div>
                <div class="info-row"><span>Approved At</span><strong>{{ $savingTransaction->approved_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
                <div class="info-row"><span>Keterangan</span><strong class="max-w-md">{{ $savingTransaction->remarks ?: '-' }}</strong></div>
            </div>
        </x-card>
    </div>

    @can('reject', $savingTransaction)
        <form method="POST"
              action="{{ route('saving-transactions.reject', $savingTransaction) }}"
              id="rejectTransactionForm"
              class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden"
                   name="reject_reason"
                   id="reject_reason">
        </form>
    @endcan

    @push('scripts')
    <script>
        document.querySelectorAll('.approve-saving-transaction-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;

                event.preventDefault();

                window.swalConfirm({
                    icon: 'question',
                    title: 'Approve Transaksi?',
                    text: 'Transaksi akan disetujui dan masuk ke saldo approved anggota.',
                    confirmButtonText: 'Ya, Approve',
                    confirmButtonColor: '#16a34a',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

		const rejectButton = document.getElementById('rejectTransactionBtn');

		if (rejectButton) {
			rejectButton.addEventListener('click', function () {

				Swal.fire({
					icon: 'warning',
					title: 'Reject Transaksi?',
					input: 'textarea',
					inputLabel: 'Alasan penolakan',
					inputPlaceholder: 'Masukkan alasan penolakan...',
					inputAttributes: {
						'aria-label': 'Masukkan alasan penolakan'
					},

					showCancelButton: true,

					confirmButtonText: 'Ya, Reject',
					cancelButtonText: 'Batal',

					confirmButtonColor: '#dc2626',

					inputValidator: (value) => {
						if (!value || !value.trim()) {
							return 'Alasan penolakan wajib diisi.';
						}
					}

				}).then((result) => {

					if (result.isConfirmed) {

						document.getElementById('reject_reason').value =
							result.value.trim();

						document.getElementById(
							'rejectTransactionForm'
						).submit();
					}

				});

			});
		}

    </script>
    @endpush
</x-app-layout>
