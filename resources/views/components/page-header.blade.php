@props([
    'title',
    'description' => null,
])

<div class="page-header">
    <div>
        <h1 class="page-header-title">{{ $title }}</h1>
        @if($description)
            <p class="page-header-description">{{ $description }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="page-header-actions">
            {{ $actions }}
        </div>
    @endif
</div>
