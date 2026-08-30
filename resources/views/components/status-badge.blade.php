@props(['status'])

@php
    $value = strtoupper((string) $status);

    $class = match ($value) {
        'ACTIVE', 'APPROVED', 'SUCCESS', 'PAID', 'PAID_OFF' => 'badge badge-success',
        'INACTIVE', 'REJECTED', 'FAILED', 'CANCELLED' => 'badge badge-danger',
        'PENDING', 'SUBMITTED', 'DRAFT' => 'badge badge-warning',
        default => 'badge badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $status }}
</span>
