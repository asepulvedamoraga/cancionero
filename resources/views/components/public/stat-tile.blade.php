@props([
    'label',
    'value',
])

<article {{ $attributes->class(['public-stat-tile']) }}>
    <p class="public-stat-tile__label">{{ $label }}</p>
    <p class="public-stat-tile__value">{{ $value }}</p>
</article>
