<x-app-layout>
    <x-slot name="title">Pelunasan Dini</x-slot>

    <x-page-header
        title="Pelunasan Dini"
        description="{{ $loan->loan_no }} - {{ $loan->member->name ?? '-' }}" />

    <div class="mx-auto max-w-5xl">
        <form
            method="POST"
            action="{{ route('loans.early-repayments.store', $loan) }}"
            enctype="multipart/form-data"
            class="early-repayment-form"
            data-loan-no="{{ $loan->loan_no }}"
            data-total="Rp {{ number_format((float) $snapshot['total_repayment'], 0, ',', '.') }}">
            @csrf

            <div class="space-y-6">
                <x-card
                    title="Informasi Pinjaman"
                    description="Pelunasan Dini hanya membayar seluruh Sisa Pokok. Sisa bunga, bunga masa depan, dan denda tidak dibebankan.">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="form-label">No. Pinjaman</label>
                            <input type="text" value="{{ $loan->loan_no }}" class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Anggota</label>
                            <input type="text" value="{{ $loan->member->name ?? '-' }}" class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Jumlah Pinjaman</label>
                            <input type="text"
                                   value="Rp {{ number_format((float) $snapshot['principal_amount'], 0, ',', '.') }}"
                                   class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Tenor</label>
                            <input type="text" value="{{ $snapshot['tenor_months'] }} bulan" class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Tgl Mulai Angsuran</label>
                            <input type="text"
                                   value="{{ $snapshot['installment_start_date']?->format('d/m/Y') ?? '-' }}"
                                   class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Tgl Akhir Angsuran</label>
                            <input type="text"
                                   value="{{ $snapshot['installment_end_date']?->format('d/m/Y') ?? '-' }}"
                                   class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Jumlah Pokok yang Sudah Dibayarkan</label>
                            <input type="text"
                                   value="Rp {{ number_format((float) $snapshot['principal_paid_to_date'], 0, ',', '.') }}"
                                   class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Jumlah Bunga yang Sudah Dibayarkan</label>
                            <input type="text"
                                   value="Rp {{ number_format((float) $snapshot['interest_paid_to_date'], 0, ',', '.') }}"
                                   class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Sisa Pokok</label>
                            <input type="text"
                                   value="Rp {{ number_format((float) $snapshot['outstanding_principal'], 0, ',', '.') }}"
                                   class="form-control font-bold" disabled>
                        </div>

                        <div>
                            <label class="form-label">Sisa Bunga (Total)</label>
                            <input type="text"
                                   value="Rp {{ number_format((float) $snapshot['outstanding_interest'], 0, ',', '.') }}"
                                   class="form-control" disabled>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Hanya informasi. Sisa bunga tidak dibayarkan pada Pelunasan Dini.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-5
                                dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <div class="text-xs font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
                            Total Pelunasan Dini
                        </div>
                        <div class="mt-1 text-2xl font-extrabold text-emerald-700 dark:text-emerald-200">
                            Rp {{ number_format((float) $snapshot['total_repayment'], 0, ',', '.') }}
                        </div>
                        <div class="mt-1 text-sm text-emerald-700/80 dark:text-emerald-200/80">
                            Nominal ini sama dengan Sisa Pokok pinjaman.
                        </div>
                    </div>
                </x-card>

                <x-card
                    title="Rekening Tujuan Pelunasan"
                    description="Transfer nominal Pelunasan Dini ke rekening koperasi berikut, kemudian upload bukti pembayarannya.">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="form-label">Nama Bank</label>
                            <input type="text" value="{{ $setting->bank_name }}" class="form-control" disabled>
                        </div>

                        <div>
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" value="{{ $setting->account_no }}" class="form-control font-bold" disabled>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Akun Akuntansi Penerima</label>
                            <input type="text"
                                   value="{{ $bankAccount->code }} - {{ $bankAccount->name }}"
                                   class="form-control" disabled>
                        </div>
                    </div>
                </x-card>

                <x-card
                    title="Bukti & Pengajuan"
                    description="Semua nilai transaksi ditentukan sistem. Anda hanya perlu mengupload bukti transfer/pelunasan.">

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="form-label">Catatan</label>
                            <textarea rows="3" class="form-textarea" readonly>{{ $notes }}</textarea>
                        </div>

                        <div>
                            <label for="payment_proof" class="form-label">
                                Upload Bukti Bayar / Pelunasan <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="payment_proof"
                                name="payment_proof"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                required
                                class="form-control">
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                JPG/JPEG/PNG/WEBP, maksimal 5 MB.
                            </p>
                            @error('payment_proof')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            <div id="proof-preview-wrap"
                                 class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                                <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Preview Bukti Pelunasan
                                </div>
                                <img id="proof-preview" alt="Preview bukti pelunasan" class="max-h-96 max-w-full rounded-lg object-contain">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5
                                sm:flex-row sm:justify-end dark:border-slate-800">
                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Pengajuan Pelunasan
                        </button>
                    </div>
                </x-card>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('payment_proof');
                const preview = document.getElementById('proof-preview');
                const wrap = document.getElementById('proof-preview-wrap');

                if (input && preview && wrap) {
                    input.addEventListener('change', function () {
                        const file = this.files && this.files[0];
                        if (!file) {
                            wrap.classList.add('hidden');
                            return;
                        }

                        preview.src = URL.createObjectURL(file);
                        wrap.classList.remove('hidden');
                    });
                }

                document.querySelectorAll('.early-repayment-form').forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        if (!window.swalConfirm) return;

                        event.preventDefault();

                        window.swalConfirm({
                            icon: 'question',
                            title: 'Ajukan Pelunasan Dini?',
                            html:
                                `Pinjaman <strong>${escapeEarlyRepaymentHtml(form.dataset.loanNo)}</strong><br>` +
                                `akan diajukan untuk Pelunasan Dini sebesar <strong>${escapeEarlyRepaymentHtml(form.dataset.total)}</strong>.<br><br>` +
                                `Setelah diajukan, pembayaran angsuran, TopUp, dan Transaksi Bulk akan dikunci sampai Pengurus melakukan Approve atau Reject.`,
                            confirmButtonText: 'Ya, Ajukan',
                            confirmButtonColor: '#4f46e5',
                        }).then((result) => {
                            if (result.isConfirmed) form.submit();
                        });
                    });
                });

                function escapeEarlyRepaymentHtml(value) {
                    const div = document.createElement('div');
                    div.textContent = String(value || '');
                    return div.innerHTML;
                }
            })();
        </script>
    @endpush
</x-app-layout>
