@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $description || isset($headerActions))
        <div class="card-header">
            <div class="card-header-row">
                <div>
                    @if($title)
                        <h2 class="card-title">{{ $title }}</h2>
                    @endif
                    @if($description)
                        <p class="card-description">{{ $description }}</p>
                    @endif
                </div>

                @if(isset($headerActions))
                    <div class="card-header-actions">
                        {{ $headerActions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>
