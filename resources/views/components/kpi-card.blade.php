@props([
    'label',
    'value',
    'caption',
    'icon',
    'trend' => null,
    'trendTone' => 'success',
    'tone' => 'default',
])

@php
    $iconBoxClass = match ($tone) {
        'warning' => 'bg-amber-50 text-amber-600',
        'danger' => 'bg-rose-50 text-rose-600',
        default => 'bg-slate-100 text-brand-600',
    };
@endphp

<article class="panel-card p-5">
    <div class="flex items-start justify-between gap-4">
        <span class="icon-chip {{ $iconBoxClass }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>

        @if ($trend)
            <x-badge :tone="$trendTone">{{ $trend }}</x-badge>
        @endif
    </div>

    <div class="mt-6">
        <p class="muted-label">{{ $label }}</p>
        <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ $value }}</p>
        <p class="mt-2 text-sm text-slate-500">{{ $caption }}</p>
    </div>
</article>
