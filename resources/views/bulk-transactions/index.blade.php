<x-app-layout>
    <x-slot name="title">Transaksi Bulk</x-slot>
    <x-page-header title="Transaksi Bulk" description="Proses potong gaji simpanan dan angsuran anggota secara massal." />

    <x-card>
        <form method="GET" action="{{ route('bulk-transactions.index') }}"
              class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[180px_180px_1fr_auto] md:items-end">
            <div><label for="month" class="form-label">Bulan</label><select id="month" name="month" class="form-select" required>
                @foreach(range(1,12) as $option)<option value="{{ $option }}" @selected($month === $option)>{{ \Carbon\Carbon::create(null,$option,1)->translatedFormat('F') }}</option>@endforeach
            </select></div>
            <div><label for="year" class="form-label">Tahun</label><select id="year" name="year" class="form-select" required>
                @foreach(range(now()->year + 1, now()->year - 10) as $option)<option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>@endforeach
            </select></div>
            <div><label for="branch_id" class="form-label">Cabang</label>
                @if($isSuperAdmin)
                    <select id="branch_id" name="branch_id" class="form-select" required><option value="">Pilih Cabang</option>
                        @foreach($branches as $option)<option value="{{ $option->id }}" @selected((string)request('branch_id') === (string)$option->id)>{{ $option->code }} - {{ $option->name }}</option>@endforeach
                    </select>
                @else
                    <input class="form-control" value="{{ $currentBranch?->code }} - {{ $currentBranch?->name }}" disabled>
                @endif
            </div>
            <div class="flex gap-2"><button class="btn btn-primary">Tampilkan</button><a href="{{ route('bulk-transactions.index') }}" class="btn btn-secondary">Reset</a></div>
        </form>

        @can('bulk-transaction.process')
            @if(!$branch)
                <div class="empty-state">Pilih periode dan cabang untuk menampilkan transaksi.</div>
            @else
                <div class="mb-5 border-b border-slate-200 pb-4 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">TRANSAKSI BULK POTONGAN ANGGOTA</h2>
                    <div class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $branch->code }} - {{ $branch->name }}</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Periode: {{ \Carbon\Carbon::create($year,$month,1)->translatedFormat('F Y') }} · {{ $report['memberCount'] }} anggota · {{ $report['selectableCount'] }} dapat diproses</div>
                </div>

                <form method="POST" action="{{ route('bulk-transactions.store') }}" id="bulk-form">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}"><input type="hidden" name="year" value="{{ $year }}">
                    @if($isSuperAdmin)<input type="hidden" name="branch_id" value="{{ $branch->id }}">@endif
					<div class="hidden md:block table-wrapper bulk-table-wrapper">
                        <table class="data-table min-w-[2150px]">
                            @include('bulk-transactions.partials.table-head')
                            <tbody>
                            @forelse($report['groups'] as $group)
                                <tr class="bg-indigo-50 dark:bg-indigo-950/40">
                                    <td class="bulk-sticky-check"></td><td class="bulk-sticky-no"></td>
                                    <td class="bulk-sticky-member font-bold text-indigo-700 dark:text-indigo-300">GROUP: {{ $group['name'] }}</td>
                                    <td colspan="17"></td>
                                </tr>
                                @foreach($group['rows'] as $row) @include('bulk-transactions.partials.web-row', ['row' => $row]) @endforeach
                                @include('bulk-transactions.partials.web-total-row', ['label'=>'SUBTOTAL '.$group['name'],'totals'=>$group['subtotal'],'class'=>'bg-slate-100 font-semibold dark:bg-slate-800'])
                            @empty
                                <tr><td colspan="20" class="empty-state">Tidak ada anggota untuk periode dan cabang ini.</td></tr>
                            @endforelse
                            @if($report['memberCount'] > 0)
                                @include('bulk-transactions.partials.web-total-row', ['label'=>'JUMLAH KESELURUHAN','totals'=>$report['totals'],'class'=>'bg-indigo-100 font-bold dark:bg-indigo-950/60'])
                            @endif
                            </tbody>
                        </table>
                    </div>

					{{-- =========================================================
						 MOBILE VIEW
					========================================================= --}}
					<div class="md:hidden">

						<div class="mb-4 flex items-center justify-between rounded-xl
									border border-slate-200 bg-white px-4 py-3
									dark:border-slate-700 dark:bg-slate-900">

							<div>
								<div class="font-semibold text-slate-900 dark:text-white">
									Pilih Anggota
								</div>

								<div class="text-xs text-slate-500 dark:text-slate-400">
									Centang anggota yang akan diproses
								</div>
							</div>

							<label class="flex cursor-pointer items-center gap-2">
								<input
									type="checkbox"
									id="check-all-mobile"
									class="h-5 w-5 rounded border-slate-300
										   text-indigo-600 focus:ring-indigo-500"
								>

								<span class="text-sm font-semibold text-slate-700
											 dark:text-slate-300">
									Semua
								</span>
							</label>

						</div>

						@forelse($report['groups'] as $group)

							{{-- GROUP HEADER --}}
							<div class="mb-3 rounded-xl border border-indigo-200
										bg-indigo-50 px-4 py-3
										dark:border-indigo-900 dark:bg-indigo-950/40">

								<div class="flex items-center justify-between gap-3">

									<div>
										<div class="text-xs font-semibold uppercase tracking-wide
													text-indigo-500 dark:text-indigo-400">
											Group
										</div>

										<div class="font-bold text-indigo-700 dark:text-indigo-300">
											{{ $group['name'] }}
										</div>
									</div>

									<div class="text-sm font-semibold text-indigo-600
												dark:text-indigo-400">
										{{ count($group['rows']) }} anggota
									</div>

								</div>
							</div>


							{{-- MEMBER CARDS --}}
							<div class="space-y-3">

								@foreach($group['rows'] as $row)

									@include('bulk-transactions.partials.mobile-row', [
										'row' => $row
									])

								@endforeach

							</div>


							{{-- SUBTOTAL GROUP --}}
							<div class="mb-6 mt-3 rounded-xl border border-slate-200
										bg-slate-50 px-4 py-4
										dark:border-slate-700 dark:bg-slate-800">

								<div class="flex items-center justify-between gap-4">

									<span class="text-sm font-semibold text-slate-700
												 dark:text-slate-300">
										SUBTOTAL {{ $group['name'] }}
									</span>

									<span class="text-base font-bold text-slate-900
												 dark:text-white">
										Rp {{ number_format(
											(float) ($group['subtotal']['all_total'] ?? 0),
											0,
											',',
											'.'
										) }}
									</span>

								</div>

							</div>

						@empty

							<div class="empty-state">
								Tidak ada anggota untuk periode dan cabang ini.
							</div>

						@endforelse


						{{-- GRAND TOTAL --}}
						@if($report['memberCount'] > 0)

							<div class="rounded-xl border border-indigo-200
										bg-indigo-50 px-4 py-4
										dark:border-indigo-900 dark:bg-indigo-950/40">

								<div class="text-xs font-bold uppercase tracking-wide
											text-indigo-500 dark:text-indigo-400">
									Jumlah Keseluruhan
								</div>

								<div class="mt-1 text-xl font-bold text-indigo-700
											dark:text-indigo-300">
									Rp {{ number_format(
										(float) ($report['totals']['all_total'] ?? 0),
										0,
										',',
										'.'
									) }}
								</div>

							</div>

						@endif

					</div>

                    <div class="mt-5 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-end dark:border-slate-800">
                        <div class="w-full sm:w-56"><label for="transaction_date" class="form-label">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" id="transaction_date" name="transaction_date" required class="form-control"
                                   min="{{ $report['periodStart']->format('Y-m-d') }}" max="{{ $report['periodEnd']->format('Y-m-d') }}" value="{{ $defaultTransactionDate }}">
                            @error('transaction_date')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary" @disabled($report['selectableCount'] === 0)>Submit</button>
                    </div>
                    @error('member_ids')<p class="form-error mt-2">{{ $message }}</p>@enderror
                </form>
            @endif
        @endcan
    </x-card>

    <div class="mt-6"><x-card title="Riwayat Transaksi Bulk" description="Batch transaksi potong gaji yang telah diproses.">
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>No. Batch</th><th>Cabang</th><th>Periode</th><th>Tanggal</th><th class="text-right">Anggota</th><th class="text-right">Total</th><th>Diproses Oleh</th><th></th></tr></thead><tbody>
        @forelse($batches as $batch)<tr><td class="font-semibold">{{ $batch->batch_no }}</td><td>{{ $batch->branch?->code }} - {{ $batch->branch?->name }}</td><td>{{ $batch->period }}</td><td>{{ $batch->transaction_date?->format('d/m/Y') }}</td><td class="text-right">{{ $batch->member_count }}</td><td class="text-right font-semibold">Rp {{ number_format((float)$batch->grand_total,0,',','.') }}</td><td>{{ $batch->processor?->name ?? '-' }}</td><td class="text-right"><a class="btn btn-secondary" href="{{ route('bulk-transactions.show',$batch) }}">Detail</a></td></tr>
        @empty<tr><td colspan="8" class="empty-state">Belum ada riwayat Transaksi Bulk.</td></tr>@endforelse
        </tbody></table></div><div class="mt-4">{{ $batches->links() }}</div>
    </x-card></div>

    <style>
        .bulk-table-wrapper { position: relative; overflow-x: auto; }
        .bulk-sticky-check,.bulk-sticky-no,.bulk-sticky-member { position: sticky; background: white; z-index: 2; }
        .dark .bulk-sticky-check,.dark .bulk-sticky-no,.dark .bulk-sticky-member { background: rgb(15 23 42); }
        .bulk-sticky-check { left: 0; width: 52px; min-width: 52px; max-width: 52px; }
        .bulk-sticky-no { left: 52px; width: 64px; min-width: 64px; max-width: 64px; }
        .bulk-sticky-member { left: 116px; width: 220px; min-width: 220px; max-width: 220px; box-shadow: 5px 0 7px -7px rgb(15 23 42 / .65); }
        thead .bulk-sticky-check,thead .bulk-sticky-no,thead .bulk-sticky-member { z-index: 4; background: rgb(241 245 249); }
        .dark thead .bulk-sticky-check,.dark thead .bulk-sticky-no,.dark thead .bulk-sticky-member { background: rgb(30 41 59); }
    </style>

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('bulk-form');

        if (!form) {
            return;
        }

        const checkAll = document.getElementById('check-all');
		
		const checkAllMobile = document.getElementById('check-all-mobile');

        const desktopBoxes = [
            ...form.querySelectorAll(
                '.bulk-table-wrapper .member-checkbox:not(:disabled)'
            )
        ];

        const mobileBoxes = [
            ...form.querySelectorAll(
                '.member-checkbox-mobile:not(:disabled)'
            )
        ];


        /*
         * -----------------------------------------------------
         * Tentukan layout aktif
         * -----------------------------------------------------
         */
        const isMobile = () => {
            return window.matchMedia('(max-width: 767px)').matches;
        };


        /*
         * -----------------------------------------------------
         * Aktifkan checkbox hanya pada layout yang sedang tampil.
         *
         * Ini penting supaya member_ids[] tidak terkirim dua kali.
         * -----------------------------------------------------
         */
        const syncActiveLayout = () => {

            if (isMobile()) {

                desktopBoxes.forEach(box => {
                    box.disabled = true;
                });

                mobileBoxes.forEach(box => {
                    box.disabled = false;
                });

            } else {

                mobileBoxes.forEach(box => {
                    box.disabled = true;
                });

                desktopBoxes.forEach(box => {
                    box.disabled = false;
                });

            }
        };


        /*
         * -----------------------------------------------------
         * Sinkron checkbox desktop <-> mobile
         * berdasarkan member_id
         * -----------------------------------------------------
         */
        const syncMember = (source) => {

            const memberId = source.value;

            form
                .querySelectorAll(
                    `.member-checkbox[value="${memberId}"]`
                )
                .forEach(box => {

                    if (box !== source) {
                        box.checked = source.checked;
                    }

                });
        };


        /*
         * -----------------------------------------------------
         * Update checkbox Pilih Semua desktop
         * -----------------------------------------------------
         */
        const syncCheckAll = () => {

			/*
			 * Checkbox Pilih Semua Desktop
			 */
			if (checkAll) {

				const selectedDesktop =
					desktopBoxes.filter(box => box.checked).length;

				checkAll.checked =
					desktopBoxes.length > 0 &&
					selectedDesktop === desktopBoxes.length;

				checkAll.indeterminate =
					selectedDesktop > 0 &&
					selectedDesktop < desktopBoxes.length;
			}

			/*
			 * Checkbox Pilih Semua Mobile
			 */
			if (checkAllMobile) {

				const selectedMobile =
					mobileBoxes.filter(box => box.checked).length;

				checkAllMobile.checked =
					mobileBoxes.length > 0 &&
					selectedMobile === mobileBoxes.length;

				checkAllMobile.indeterminate =
					selectedMobile > 0 &&
					selectedMobile < mobileBoxes.length;
			}

        };


        /*
         * -----------------------------------------------------
         * Event checkbox anggota
         * -----------------------------------------------------
         */
        [...desktopBoxes, ...mobileBoxes].forEach(box => {

            box.addEventListener('change', () => {

                syncMember(box);

                syncCheckAll();

            });

        });


        /*
         * -----------------------------------------------------
         * Pilih semua
         * -----------------------------------------------------
         */
        if (checkAll) {

            checkAll.addEventListener('change', () => {

                desktopBoxes.forEach(box => {
                    box.checked = checkAll.checked;
                });

                mobileBoxes.forEach(box => {
                    box.checked = checkAll.checked;
                });

                syncCheckAll();

            });

        }

		if (checkAllMobile) {

			checkAllMobile.addEventListener('change', () => {

				mobileBoxes.forEach(box => {
					box.checked = checkAllMobile.checked;
				});

				desktopBoxes.forEach(box => {
					box.checked = checkAllMobile.checked;
				});

				syncCheckAll();

			});

		}

        /*
         * -----------------------------------------------------
         * Saat ukuran layar berubah
         * -----------------------------------------------------
         */
        window.addEventListener('resize', () => {

            syncActiveLayout();

            syncCheckAll();

        });


        /*
         * -----------------------------------------------------
         * Initial state
         * -----------------------------------------------------
         */
        syncActiveLayout();

        syncCheckAll();


        /*
         * -----------------------------------------------------
         * Submit
         * -----------------------------------------------------
         */
        form.addEventListener('submit', event => {

            /*
             * Pastikan layout yang aktif saja yang enabled.
             */
            syncActiveLayout();

            const activeBoxes = isMobile()
                ? mobileBoxes
                : desktopBoxes;

            const count = activeBoxes.filter(box => box.checked).length;


            if (!count) {

                event.preventDefault();

                if (window.Swal) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum ada anggota dipilih',
                        text: 'Centang minimal satu anggota.'
                    });

                } else {

                    alert('Centang minimal satu anggota.');

                }

                return;
            }


            if (!window.swalConfirm) {
                return;
            }


            event.preventDefault();


            window.swalConfirm({
                icon: 'question',
                title: 'Apakah semua data sudah benar?',
                html: `<strong>${count}</strong> anggota akan diproses.`,
                confirmButtonText: 'Ok Submit',
                cancelButtonText: 'Tidak',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5'
            }).then(result => {

                if (result.isConfirmed) {

                    /*
                     * Pastikan lagi hanya checkbox layout aktif
                     * yang dikirim ke server.
                     */
                    syncActiveLayout();

                    form.submit();
                }

            });

        });

    })();
</script>
@endpush

</x-app-layout>
