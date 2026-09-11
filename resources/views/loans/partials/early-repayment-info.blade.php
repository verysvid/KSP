@can('early-repayment.view')
    @php
        $latestEarlyRepayment = $loan->earlyRepayments()
            ->with(['submitter', 'approver', 'rejecter', 'journalEntry'])
            ->latest('id')
            ->first();
    @endphp

    @if($latestEarlyRepayment)
        @php
            $isSubmitted = $latestEarlyRepayment->status === \App\Models\LoanEarlyRepayment::STATUS_SUBMITTED;
            $isApproved = $latestEarlyRepayment->status === \App\Models\LoanEarlyRepayment::STATUS_APPROVED;
            $isRejected = $latestEarlyRepayment->status === \App\Models\LoanEarlyRepayment::STATUS_REJECTED;
        @endphp

        <div class="mb-6">
            <x-card
                title="Pelunasan Dini"
                description="Riwayat pengajuan dan verifikasi pelunasan dipercepat untuk pinjaman ini.">

                @if($isSubmitted)
                    <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3
                                text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                        <div class="font-bold">Menunggu Verifikasi / Approval Pengurus</div>
                        <div class="mt-1">
                            Pembayaran angsuran, TopUp, dan Transaksi Bulk cabang sementara dikunci sampai pengajuan ini di-Approve atau di-Reject.
                        </div>
                    </div>
                @elseif($isApproved)
                    <div class="mb-5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3
                                text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                        <div class="font-bold">Pelunasan Dini Telah Disetujui</div>
                        <div class="mt-1">Sisa pokok telah dilunasi dan pinjaman berstatus PAID_OFF.</div>
                    </div>
                @elseif($isRejected)
                    <div class="mb-5 rounded-xl border border-red-300 bg-red-50 px-4 py-3
                                text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                        <div class="font-bold">Pengajuan Pelunasan Dini Ditolak</div>
                        <div class="mt-1">Pinjaman kembali dapat diproses secara normal selama masih berstatus ACTIVE.</div>
                    </div>
                @endif

                <div class="info-list">
                    <div class="info-row">
                        <span>No. Pelunasan</span>
                        <strong>{{ $latestEarlyRepayment->repayment_no }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Tanggal Pengajuan</span>
                        <strong>{{ $latestEarlyRepayment->request_date?->format('d/m/Y') ?? '-' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Sisa Pokok</span>
                        <strong>Rp {{ number_format((float) $latestEarlyRepayment->outstanding_principal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Sisa Bunga (Informasi)</span>
                        <strong>Rp {{ number_format((float) $latestEarlyRepayment->outstanding_interest, 0, ',', '.') }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Total Pelunasan Dini</span>
                        <strong class="text-emerald-700 dark:text-emerald-300">
                            Rp {{ number_format((float) $latestEarlyRepayment->total_repayment, 0, ',', '.') }}
                        </strong>
                    </div>
                    <div class="info-row">
                        <span>Rekening Tujuan</span>
                        <strong>{{ $latestEarlyRepayment->bank_name }} - {{ $latestEarlyRepayment->account_no }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Bukti Pelunasan</span>
                        <strong>
                            <a href="{{ route('loans.early-repayments.proof', [$loan, $latestEarlyRepayment]) }}"
                               target="_blank"
                               class="text-indigo-600 hover:underline dark:text-indigo-400">
                                Lihat Bukti
                            </a>
                        </strong>
                    </div>
                    <div class="info-row">
                        <span>Status</span>
                        <strong>{{ $latestEarlyRepayment->status }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Diajukan Oleh</span>
                        <strong>{{ $latestEarlyRepayment->submitter?->name ?? '-' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Catatan</span>
                        <strong class="max-w-md">{{ $latestEarlyRepayment->notes ?: '-' }}</strong>
                    </div>

                    @if($isApproved)
                        <div class="info-row">
                            <span>Disetujui Oleh</span>
                            <strong>{{ $latestEarlyRepayment->approver?->name ?? '-' }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Waktu Approval</span>
                            <strong>{{ $latestEarlyRepayment->approved_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </div>
                        @if($latestEarlyRepayment->journalEntry && auth()->user()?->can('journal.view'))
                            <div class="info-row">
                                <span>Jurnal</span>
                                <strong>
                                    <a href="{{ route('journal-entries.show', $latestEarlyRepayment->journalEntry) }}"
                                       class="text-indigo-600 hover:underline dark:text-indigo-400">
                                        {{ $latestEarlyRepayment->journalEntry->journal_no }}
                                    </a>
                                </strong>
                            </div>
                        @endif
                    @endif

                    @if($isRejected)
                        <div class="info-row">
                            <span>Ditolak Oleh</span>
                            <strong>{{ $latestEarlyRepayment->rejecter?->name ?? '-' }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Waktu Penolakan</span>
                            <strong>{{ $latestEarlyRepayment->rejected_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Alasan Penolakan</span>
                            <strong class="max-w-md text-red-600 dark:text-red-400">
                                {{ $latestEarlyRepayment->rejection_reason ?: '-' }}
                            </strong>
                        </div>
                    @endif
                </div>

                @if($isSubmitted && (auth()->user()?->can('early-repayment.approve') || auth()->user()?->can('early-repayment.reject')))
                    <div class="mt-6 grid grid-cols-1 gap-5 border-t border-slate-200 pt-5 lg:grid-cols-2 dark:border-slate-800">
                        @can('early-repayment.reject')
                            <form method="POST"
                                  action="{{ route('loans.early-repayments.reject', [$loan, $latestEarlyRepayment]) }}"
                                  class="early-repayment-reject-form"
                                  data-repayment-no="{{ $latestEarlyRepayment->repayment_no }}">
                                @csrf
                                @method('PATCH')
                                <label for="early_repayment_rejection_reason" class="form-label">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea id="early_repayment_rejection_reason"
                                          name="rejection_reason"
                                          rows="3"
                                          required
                                          maxlength="2000"
                                          class="form-textarea"
                                          placeholder="Contoh: dana belum masuk / bukti transfer tidak valid">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')<p class="form-error">{{ $message }}</p>@enderror
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-danger">Reject Pelunasan</button>
                                </div>
                            </form>
                        @endcan

                        @can('early-repayment.approve')
                            <div class="lg:text-right">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm
                                            text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                                    Pastikan dana sebesar
                                    <strong>Rp {{ number_format((float) $latestEarlyRepayment->total_repayment, 0, ',', '.') }}</strong>
                                    sudah masuk ke rekening koperasi sebelum melakukan approval.
                                </div>
                                <form method="POST"
                                      action="{{ route('loans.early-repayments.approve', [$loan, $latestEarlyRepayment]) }}"
                                      class="early-repayment-approve-form mt-3"
                                      data-repayment-no="{{ $latestEarlyRepayment->repayment_no }}"
                                      data-total="Rp {{ number_format((float) $latestEarlyRepayment->total_repayment, 0, ',', '.') }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-primary">Approve Pelunasan</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                @endif
            </x-card>
        </div>

        @push('scripts')
            <script>
                (() => {
                    document.querySelectorAll('.early-repayment-approve-form').forEach((form) => {
                        form.addEventListener('submit', function (event) {
                            if (!window.swalConfirm) return;
                            event.preventDefault();
                            window.swalConfirm({
                                icon: 'question',
                                title: 'Approve Pelunasan Dini?',
                                html:
                                    `Pastikan dana <strong>${escapeEarlyInfoHtml(form.dataset.total)}</strong> ` +
                                    `untuk <strong>${escapeEarlyInfoHtml(form.dataset.repaymentNo)}</strong> telah diterima pada rekening koperasi.`,
                                confirmButtonText: 'Ya, Approve',
                                confirmButtonColor: '#4f46e5',
                            }).then((result) => {
                                if (result.isConfirmed) form.submit();
                            });
                        });
                    });

                    document.querySelectorAll('.early-repayment-reject-form').forEach((form) => {
                        form.addEventListener('submit', function (event) {
                            if (!window.swalConfirm) return;
                            event.preventDefault();
                            window.swalConfirm({
                                icon: 'warning',
                                title: 'Reject Pelunasan Dini?',
                                text: `Pengajuan ${form.dataset.repaymentNo} akan ditolak dan penguncian pinjaman akan dibuka kembali.`,
                                confirmButtonText: 'Ya, Reject',
                                confirmButtonColor: '#dc2626',
                            }).then((result) => {
                                if (result.isConfirmed) form.submit();
                            });
                        });
                    });

                    function escapeEarlyInfoHtml(value) {
                        const div = document.createElement('div');
                        div.textContent = String(value || '');
                        return div.innerHTML;
                    }
                })();
            </script>
        @endpush
    @endif
@endcan
