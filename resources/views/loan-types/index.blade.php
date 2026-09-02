<x-app-layout>
    @php
        $totalTypes = $totalTypes ?? \App\Models\LoanType::count();
        $activeTypes = $activeTypes ?? \App\Models\LoanType::where('is_active', true)->count();
        $effectiveTypes = $effectiveTypes ?? \App\Models\LoanType::where('interest_type', 'EFFECTIVE')->count();
    @endphp
    <x-slot name="title">Jenis Pinjaman</x-slot>

    <x-page-header
        title="Master Jenis Pinjaman"
        description="Kelola jenis pinjaman koperasi."
    >
        <x-slot:actions>
            @can('loan-type.create')
                <a href="{{ route('loan-types.create') }}" class="btn btn-primary">
                    + Tambah Jenis Pinjaman
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

	{{--
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Jenis</span>
                <div class="stat-icon">▣</div>
            </div>
            <div class="stat-value">{{ $totalTypes }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Jenis Aktif</span>
                <div class="stat-icon">✓</div>
            </div>
            <div class="stat-value">{{ $activeTypes }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Bunga Effective</span>
                <div class="stat-icon">!</div>
            </div>
            <div class="stat-value">{{ $effectiveTypes }}</div>
        </div>
    </div>
	--}}

    <x-card>
        <form method="GET" 
			  action="{{ route('loan-types.index') }}" 
			  class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[minmax(280px,1fr)_180px_180px_180px_auto]">
			<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kode, nama, deskripsi...">

			<select name="interest_type" class="form-select">
				<option value="">Semua Bunga</option>
				<option value="FLAT" @selected(request('interest_type') === 'FLAT')>Flat</option>
				<option value="EFFECTIVE" @selected(request('interest_type') === 'EFFECTIVE')>Effective</option>
			</select>

			<select name="status" class="form-select">
				<option value="">Semua Status</option>
				<option value="active" @selected(request('status') === 'active')>Aktif</option>
				<option value="inactive" @selected(request('status') === 'inactive')>Tidak Aktif</option>
			</select>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Cari</button>
                <a href="{{ route('loan-types.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="hidden md:block">
            <div class="table-wrapper">
				<table class="data-table">
					<thead>
						<tr>
							<th>Kode</th>
							<th>Nama</th>
							<th>Metode Bunga</th>
							<th>Bunga</th>
							<th>Tenor</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>
						@forelse($loanTypes as $loanType)
							<tr>
								<td>
									<span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $loanType->code }}</span>
								</td>

								<td>
									<div class="font-semibold text-gray-900 dark:text-gray-100">{{ $loanType->name }}</div>
									@if($loanType->description)
										<div class="mt-1 max-w-[360px] text-xs leading-5 text-gray-500 dark:text-gray-400">
										{{ $loanType->description }}
										</div>
									@endif
								</td>

								<td>
									{{ $loanType->interest_type === 'FLAT' ? 'Flat' : 'Effective' }}
								</td>

								<td>
									{{ number_format((float) $loanType->interest_rate, 4, ',', '.') }}%
								</td>

								<td>
									{{ $loanType->min_tenor }} - {{ $loanType->max_tenor ?? '∞' }} bulan
								</td>

								<td>
									@if($loanType->is_active)
										<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">ACTIVE</span>
									@else
										<span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">INACTIVE</span>
									@endif
								</td>

								<td>
									<div class="flex justify-end gap-2">
										@can('loan-type.view')
											<a href="{{ route('loan-types.show', $loanType) }}" class="btn btn-secondary btn-sm">Detail</a>
										@endcan

										@can('loan-type.edit')
											<a href="{{ route('loan-types.edit', $loanType) }}" class="btn btn-secondary btn-sm">Edit</a>

											<form method="POST"
												  action="{{ route('loan-types.toggle-status', $loanType) }}"
												  class="loan-type-toggle-form"
												  data-name="{{ $loanType->name }}"
												  data-active="{{ $loanType->is_active ? '1' : '0' }}">
												@csrf
												@method('PATCH')

												<button type="submit" class="btn {{ $loanType->is_active ? 'btn-danger' : 'btn-primary' }} btn-sm">
													{{ $loanType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
												</button>
											</form>
										@endcan
										
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
									Data jenis pinjaman belum tersedia.
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
            </div>
        </div>

        <div class="space-y-4 md:hidden">
            @forelse($loanTypes as $loanType)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ $loanType->code }}</div>
                            <div class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $loanType->name }}</div>

                            @if($loanType->description)
                                <div class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $loanType->description }}</div>
                            @endif
                        </div>

                        @if($loanType->is_active)
                            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">ACTIVE</span>
                        @else
                            <span class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">INACTIVE</span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Metode Bunga</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $loanType->interest_type === 'FLAT' ? 'Flat' : 'Effective' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Bunga</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $loanType->interest_rate, 4, ',', '.') }}%</div>
                        </div>

                        <div class="col-span-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tenor</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $loanType->min_tenor }} - {{ $loanType->max_tenor ?? '∞' }} bulan</div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @can('loan-type.view')
                            <a href="{{ route('loan-types.show', $loanType) }}" class="btn btn-secondary btn-sm">Detail</a>
                        @endcan

                        @can('loan-type.edit')
                            <a href="{{ route('loan-types.edit', $loanType) }}" class="btn btn-secondary btn-sm">Edit</a>

                            <form method="POST"
                                  action="{{ route('loan-types.toggle-status', $loanType) }}"
                                  class="loan-type-toggle-form"
                                  data-name="{{ $loanType->name }}"
                                  data-active="{{ $loanType->is_active ? '1' : '0' }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn {{ $loanType->is_active ? 'btn-danger' : 'btn-primary' }} btn-sm">
                                    {{ $loanType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        @endcan
						
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-gray-500 dark:text-gray-400">Data jenis pinjaman belum tersedia.</div>
            @endforelse
        </div>

        @if($loanTypes->hasPages())
            <div class="mt-6">
                {{ $loanTypes->links() }}
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script type="module">
            import { swalConfirm } from '/resources/js/sweetalert.js';

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.loan-type-toggle-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        const name = form.dataset.name || '';
                        const isActive = form.dataset.active === '1';

                        swalConfirm({
                            icon: 'question',
                            title: isActive ? 'Nonaktifkan Jenis Pinjaman?' : 'Aktifkan Jenis Pinjaman?',
                            html: 'Jenis pinjaman <strong>' + escapeHtml(name) + '</strong> akan ' + (isActive ? 'dinonaktifkan.' : 'diaktifkan.'),
                            confirmButtonText: isActive ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
                            cancelButtonText: 'Batal',
                            showCancelButton: true,
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

                function escapeHtml(value) {
                    const div = document.createElement('div');
                    div.textContent = String(value);
                    return div.innerHTML;
                }
            });
        </script>
    @endpush
</x-app-layout>
