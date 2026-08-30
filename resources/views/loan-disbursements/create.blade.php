<x-app-layout>
    <x-slot name="title">Pencairan Pinjaman</x-slot>

    <x-page-header
        title="Pencairan Pinjaman"
        description="{{ $loan->loan_no }} - {{ $loan->member->name ?? '-' }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pencairan"
            description="Konfirmasi pencairan. Setelah disimpan, pinjaman menjadi Active dan jadwal angsuran otomatis dibuat.">

            <form
                method="POST"
                action="{{ route('loans.disbursements.store', $loan) }}"
                class="loan-disbursement-form"
                data-loan-no="{{ $loan->loan_no }}">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="form-label">No. Pinjaman</label>
                        <input
                            type="text"
                            value="{{ $loan->loan_no }}"
                            class="form-control"
                            disabled>
                    </div>

                    <div>
                        <label class="form-label">Anggota</label>
                        <input
                            type="text"
                            value="{{ $loan->member->name ?? '-' }}"
                            class="form-control"
                            disabled>
                    </div>

                    <div>
                        <label class="form-label">Jenis Pinjaman</label>
                        <input
                            type="text"
                            value="{{ ($loan->loanType->code ?? '-') . ' - ' . ($loan->loanType->name ?? '-') }}"
                            class="form-control"
                            disabled>
                    </div>

                    <div>
                        <label class="form-label">Nominal Pencairan</label>
                        <input
                            type="text"
                            value="Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}"
                            class="form-control"
                            disabled>
                    </div>

                    <div>
                        <label for="disbursement_date" class="form-label">
                            Tanggal Pencairan <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="disbursement_date"
                            name="disbursement_date"
                            type="date"
                            required
                            value="{{ old('disbursement_date', now()->format('Y-m-d')) }}"
                            class="form-control">

                        @error('disbursement_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cash_account_id" class="form-label">
                            Sumber Kas / Bank <span class="text-red-500">*</span>
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
                            placeholder="No. bukti / transfer">
                    </div>

                    <div>
                        <label class="form-label">Jatuh Tempo Pertama</label>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3
                                    text-sm text-slate-700
                                    dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
                            Tanggal {{ $loan->due_day }} pada bulan setelah pencairan
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="form-label">Catatan</label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="form-textarea"
                            placeholder="Keterangan pencairan pinjaman">{{ old('notes') }}</textarea>
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
                        Proses Pencairan
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.loan-disbursement-form')
                .forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) {
                            return;
                        }

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Proses Pencairan Pinjaman?',
                            html:
                                `Pinjaman <strong>${escapeDisbursementHtml(form.dataset.loanNo)}</strong>` +
                                ` akan dicairkan. Jurnal dan jadwal angsuran akan dibuat otomatis.`,
                            confirmButtonText: 'Ya, Cairkan',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

            function escapeDisbursementHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value || '');
                return div.innerHTML;
            }
        </script>
    @endpush
</x-app-layout>
