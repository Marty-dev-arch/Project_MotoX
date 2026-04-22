@props(['value' => 0, 'tone' => 'accent'])

@php
    $fillClass = match ($tone) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'indigo' => 'bg-indigo-500',
        'teal' => 'bg-teal-500',
        'amber' => 'bg-orange-400',
        default => 'bg-brand-500',
    };
@endphp

<div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
    <div class="h-full rounded-full {{ $fillClass }}" style="width: {{ max(0, min(100, (float) $value)) }}%"></div>
</div>
