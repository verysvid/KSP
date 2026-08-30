<x-app-layout>
    <x-slot name="title">Pembayaran Angsuran</x-slot>

    <x-page-header
        title="Pembayaran Angsuran"
        description="{{ $loan->loan_no }} - Angsuran #{{ $installment->installment_no }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pembayaran"
            description="Pembayaran pada tahap ini harus dilakukan penuh sesuai nilai angsuran.">

            <form
                method="POST"
                action="{{ route('loans.installments.payments.store', [$loan, $installment]) }}"
                class="loan-payment-form"
                data-loan-no="{{ $loan->loan_no }}"
                data-installment-no="{{ $installment->installment_no }}">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="form-label">No. Pinjaman</label>
                        <input type="text"
                               value="{{ $loan->loan_no }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Anggota</label>
                        <input type="text"
                               value="{{ $loan->member->name ?? '-' }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Angsuran Ke</label>
                        <input type="text"
                               value="#{{ $installment->installment_no }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Jatuh Tempo</label>
                        <input type="text"
                               value="{{ $installment->due_date?->format('d/m/Y') }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Pokok</label>
                        <input type="text"
                               value="Rp {{ number_format((float) $installment->principal_amount, 0, ',', '.') }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Bunga</label>
                        <input type="text"
                               value="Rp {{ number_format((float) $installment->interest_amount, 0, ',', '.') }}"
                               class="form-control"
                               disabled>
                    </div>

					@php
					$isOverdue = now()->startOfDay()->gt($installment->due_date->copy()->startOfDay());
					$daysOverdue = $isOverdue ? $installment->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay()) : 0;
					$penaltyType = $loan->loanType->penalty_type ?? 'NONE';
					$estimatedPenalty = 0;
					if ($isOverdue && $penaltyType === 'FIXED') {
						$estimatedPenalty = (float) ($loan->loanType->penalty_amount ?? 0);
					}
					if ($isOverdue && $penaltyType === 'PERCENTAGE') {
						$baseAmount = (float)$installment->principal_amount + (float)$installment->interest_amount;
						$estimatedPenalty = $baseAmount * ((float)($loan->loanType->penalty_rate ?? 0) / 100);
					}
					@endphp

					<div>
						<label class="form-label">Status Keterlambatan</label>
						<div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
							@if($isOverdue)
								<strong class="text-red-600 dark:text-red-400">OVERDUE</strong> · {{ $daysOverdue }} hari terlambat
							@else
								<strong class="text-emerald-600 dark:text-emerald-400">Tepat Waktu</strong>
							@endif
						</div>
					</div>

					<div>
						<label class="form-label">Kebijakan Denda</label>
						<div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
							{{ $penaltyType }}
							@if($penaltyType === 'NONE') · Tidak dikenakan denda
							@elseif($penaltyType === 'FIXED') · Rp {{ number_format((float)($loan->loanType->penalty_amount ?? 0),0,',','.') }}
							@elseif($penaltyType === 'PERCENTAGE') · {{ number_format((float)($loan->loanType->penalty_rate ?? 0),4,',','.') }}%
							@endif
						</div>
					</div>

                    <div>
                        <label class="form-label">Denda</label>
                        <input type="text"
                               value="Rp {{ number_format((float) $estimatedPenalty, 0, ',', '.') }}"
                               class="form-control"
                               disabled>
                    </div>

                    <div>
                        <label class="form-label">Total Pembayaran</label>

                        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3
                                    text-lg font-extrabold text-indigo-700
                                    dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300">
                            Rp {{ number_format(
                                (float) $installment->principal_amount
                                + (float) $installment->interest_amount
                                + (float) $estimatedPenalty,
                                0,
                                ',',
                                '.'
                            ) }}
                        </div>
                    </div>

                    <div>
                        <label for="payment_date" class="form-label">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="payment_date"
                            name="payment_date"
                            type="date"
                            required
                            value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                            class="form-control">

                        @error('payment_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cash_account_id" class="form-label">
                            Kas / Bank Penerima <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="cash_account_id"
                            name="cash_account_id"
                            required
                            class="form-select">

                            <option value="">Pilih Kas / Bank</option>

                            @foreach($cashAccounts as $account)
                                <option
                                    value="{{ $account->id }}"
                                    @selected((string) old('cash_account_id') === (string) $account->id)>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('cash_account_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reference_no" class="form-label">
                            No. Referensi
                        </label>

                        <input
                            id="reference_no"
                            name="reference_no"
                            type="text"
                            maxlength="100"
                            value="{{ old('reference_no') }}"
                            class="form-control"
                            placeholder="No. kuitansi / transfer">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="form-label">Catatan</label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="form-textarea"
                            placeholder="Keterangan pembayaran">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5
                            sm:flex-row sm:justify-end dark:border-slate-800">
                    <a href="{{ route('loans.show', $loan) }}"
                       class="btn btn-secondary">
                        Batal
                    </a>

                    <button type="submit"
                            class="btn btn-primary">
                        Proses Pembayaran
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.loan-payment-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Proses Pembayaran Angsuran?',
                            html:
                                `Pinjaman <strong>${escapeLoanPaymentHtml(form.dataset.loanNo)}</strong>` +
                                ` angsuran <strong>#${escapeLoanPaymentHtml(form.dataset.installmentNo)}</strong>` +
                                ` akan dibayar penuh.`,
                            confirmButtonText: 'Ya, Bayar',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            function escapeLoanPaymentHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value || '');
                return div.innerHTML;
            }
        </script>
    @endpush
</x-app-layout>
