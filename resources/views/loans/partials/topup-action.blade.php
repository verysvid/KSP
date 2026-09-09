@php
    $topUpEligibility = null;

    if ($loan->status === \App\Models\Loan::STATUS_ACTIVE && auth()->user()?->can('loan.create')) {
        $topUpEligibility = app(\App\Services\LoanTopUpService::class)->eligibility($loan);
    }
@endphp

@if($loan->is_topup && $loan->status === \App\Models\Loan::STATUS_DRAFT && auth()->user()?->can('loan.edit'))
    <a href="{{ route('loans.topup.edit', $loan) }}" class="btn btn-secondary">
        Edit TopUp
    </a>
@endif

@if($topUpEligibility && $topUpEligibility['eligible'])
    <a href="{{ route('loans.topup.create', $loan) }}" class="btn btn-primary">
        TopUp
    </a>
@endif
