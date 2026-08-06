@props([
    'label',
    'value',
])

<article class="rounded-2xl border border-white/25 bg-white/10 px-4 py-3 backdrop-blur-sm">
    <p class="text-xs font-semibold uppercase tracking-wide text-cyan-100">{{ $label }}</p>
    <p class="mt-1 text-2xl font-black text-white">{{ $value }}</p>
</article>
