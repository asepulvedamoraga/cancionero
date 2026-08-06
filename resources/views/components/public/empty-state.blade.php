@props([
    'title',
    'message',
    'class' => '',
])

<div @class([
    'rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600',
    $class,
])>
    <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
    <p class="mt-2 text-sm">{{ $message }}</p>
</div>
