@php
    $earlyRepaymentEligibility = null;

    if (
        $loan->status === \App\Models\Loan::STATUS_ACTIVE
        && auth()->user()?->can('early-repayment.create')
    ) {
        $earlyRepaymentEligibility = app(\App\Services\LoanEarlyRepaymentGuardService::class)
            ->eligibility($loan);
    }
@endphp

@if($earlyRepaymentEligibility && $earlyRepaymentEligibility['eligible'])
    <a href="{{ route('loans.early-repayments.create', $loan) }}" class="btn btn-primary">
        Pelunasan
    </a>
@endif
