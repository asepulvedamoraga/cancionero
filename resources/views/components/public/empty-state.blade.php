@props([
    'title',
    'message',
    'class' => '',
    'actionLabel' => null,
    'actionUrl' => null,
])

<div @class([
    'public-empty-state text-center',
    $class,
])>
    <h2 class="public-empty-state__title">{{ $title }}</h2>
    <p class="public-empty-state__message">{{ $message }}</p>
    @if($actionLabel && $actionUrl)
        <div class="public-empty-state__actions">
            <a class="public-btn public-btn--ghost public-btn--small" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
        </div>
    @endif
</div>
