@props(['tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'accent' => 'bg-brand-50 text-brand-700 ring-brand-100',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-100',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {$classes}"]) }}>
    {{ $slot }}
</span>
