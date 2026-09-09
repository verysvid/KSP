@if($loan->is_topup)
    @php
        $sourceTopUpLoan = $loan->topUpFrom;
    @endphp

    <div class="mb-6">
        <x-card
            title="Informasi TopUp"
            description="Pinjaman ini berasal dari proses TopUp pinjaman sebelumnya.">
            <div class="info-list">
                <div class="info-row">
                    <span>Pinjaman Lama</span>
                    <strong>
                        @if($sourceTopUpLoan)
                            <a href="{{ route('loans.show', $sourceTopUpLoan) }}" class="text-indigo-600 hover:underline dark:text-indigo-400">
                                {{ $loan->old_loan_no ?? $sourceTopUpLoan->loan_no }}
                            </a>
                        @else
                            {{ $loan->old_loan_no ?? '-' }}
                        @endif
                    </strong>
                </div>
                <div class="info-row">
                    <span>Nominal TopUp / Dana Baru</span>
                    <strong>Rp {{ number_format((float) $loan->topup_amount, 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Sisa Pokok yang Direfinance</span>
                    <strong>Rp {{ number_format(max(0, (float) $loan->principal_amount - (float) $loan->topup_amount), 0, ',', '.') }}</strong>
                </div>
                <div class="info-row">
                    <span>Total Pinjaman Baru</span>
                    <strong>Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }}</strong>
                </div>
            </div>
        </x-card>
    </div>
@elseif($loan->status === \App\Models\Loan::STATUS_PAID_OFF)
    @php
        $replacementTopUp = $loan->topUps()
            ->whereIn('status', [\App\Models\Loan::STATUS_ACTIVE, \App\Models\Loan::STATUS_PAID_OFF])
            ->latest('id')
            ->first();
    @endphp

    @if($replacementTopUp)
        <div class="mb-6">
            <x-card title="Riwayat TopUp" description="Pinjaman ini telah ditutup melalui proses TopUp.">
                <div class="info-list">
                    <div class="info-row">
                        <span>Pinjaman Pengganti</span>
                        <strong>
                            <a href="{{ route('loans.show', $replacementTopUp) }}" class="text-indigo-600 hover:underline dark:text-indigo-400">
                                {{ $replacementTopUp->loan_no }}
                            </a>
                        </strong>
                    </div>
                    <div class="info-row">
                        <span>Dana TopUp</span>
                        <strong>Rp {{ number_format((float) $replacementTopUp->topup_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </x-card>
        </div>
    @endif
@endif
