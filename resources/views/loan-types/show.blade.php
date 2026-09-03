<x-app-layout>
    <x-page-header
        title="Detail Jenis Pinjaman"
        description="Informasi lengkap master jenis pinjaman."
    >
        <x-slot:actions>
            <a
                href="{{ route('loan-types.edit', $loanType) }}"
                class="btn btn-primary"
            >
                Edit
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Kode
                        </div>
                        <div class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                            {{ $loanType->code }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Nama
                        </div>
                        <div class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                            {{ $loanType->name }}
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Deskripsi
                        </div>
                        <div class="mt-1 text-gray-700 dark:text-gray-300">
                            {{ $loanType->description ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Metode Bunga
                        </div>
                        <div class="mt-1">
                            {{ $loanType->interest_type === 'FLAT' ? 'Flat' : 'Effective' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Bunga per Bulan
                        </div>
                        <div class="mt-1">
							{{ str_replace('.', ',', rtrim(rtrim(number_format((float) $loanType->interest_rate, 4, '.', ''), '0'), '.')) }}%
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Minimum Pinjaman
                        </div>
                        <div class="mt-1">
                            {{ $loanType->min_amount !== null ? 'Rp '.number_format((float) $loanType->min_amount, 0, ',', '.') : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Maksimum Pinjaman
                        </div>
                        <div class="mt-1">
                            {{ $loanType->max_amount !== null ? 'Rp '.number_format((float) $loanType->max_amount, 0, ',', '.') : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Minimum Tenor
                        </div>
                        <div class="mt-1">
                            {{ $loanType->min_tenor }} bulan
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Maksimum Tenor
                        </div>
                        <div class="mt-1">
                            {{ $loanType->max_tenor !== null ? $loanType->max_tenor.' bulan' : '-' }}
                        </div>
                    </div>
                </div>
            </x-card>
			
			<x-card title="Mapping Chart of Accounts" class="mt-5">
				<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
					<div>
						<div class="text-sm text-slate-500 dark:text-slate-400">
							Piutang Pinjaman
						</div>
						<div class="mt-1 font-semibold text-slate-900 dark:text-white">
							@if($loanType->receivableAccount)
								{{ $loanType->receivableAccount->code }} - {{ $loanType->receivableAccount->name }}
							@else
								Belum dimapping
							@endif
						</div>
					</div>

					<div>
						<div class="text-sm text-slate-500 dark:text-slate-400">
							Pendapatan Bunga
						</div>
						<div class="mt-1 font-semibold text-slate-900 dark:text-white">
							@if($loanType->interestIncomeAccount)
								{{ $loanType->interestIncomeAccount->code }} - {{ $loanType->interestIncomeAccount->name }}
							@else
								Belum dimapping
							@endif
						</div>
					</div>

					<div>
						<div class="text-sm text-slate-500 dark:text-slate-400">
							Pendapatan Denda
						</div>
						<div class="mt-1 font-semibold text-slate-900 dark:text-white">
							@if($loanType->penaltyIncomeAccount)
								{{ $loanType->penaltyIncomeAccount->code }} - {{ $loanType->penaltyIncomeAccount->name }}
							@elseif(($loanType->penalty_type ?? 'NONE') === 'NONE')
								Tidak digunakan
							@else
								Belum dimapping
							@endif
						</div>
					</div>
				</div>

			</x-card>
        </div>

        <div>
            <x-card>
                <div class="space-y-5">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Status
                        </div>

                        <div class="mt-2">
                            @if($loanType->is_active)
                                <span class="status-badge status-success">
                                    Aktif
                                </span>
                            @else
                                <span class="status-badge status-danger">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Tipe Denda
                        </div>
                        <div class="mt-1">
                            @switch($loanType->penalty_type)
                                @case('FIXED')
                                    Nominal Tetap
                                    @break
                                @case('PERCENTAGE')
                                    Persentase
                                    @break
                                @default
                                    Tidak Ada
                            @endswitch
                        </div>
                    </div>

                    @if($loanType->penalty_type === 'FIXED')
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Nominal Denda
                            </div>
                            <div class="mt-1">
                                Rp {{ number_format((float) $loanType->penalty_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    @endif

                    @if($loanType->penalty_type === 'PERCENTAGE')
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Persentase Denda
                            </div>
                            <div class="mt-1">
                                {{ number_format((float) $loanType->penalty_rate, 4, ',', '.') }}%
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-5 dark:border-gray-700">
                        <a
                            href="{{ route('loan-types.index') }}"
                            class="btn btn-secondary w-full text-center"
                        >
                            Kembali
                        </a>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
