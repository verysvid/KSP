@props([
    'title' => 'Belum ada data',
    'description' => null,
])

<div class="empty-state-box">
    <div class="empty-state-icon">◎</div>
    <strong>{{ $title }}</strong>

    @if($description)
        <p>{{ $description }}</p>
    @endif

    @if(isset($action))
        <div class="empty-state-action">
            {{ $action }}
        </div>
    @endif
</div>
