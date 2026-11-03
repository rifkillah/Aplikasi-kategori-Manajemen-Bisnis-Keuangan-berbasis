@props(['label', 'value', 'icon' => 'chart', 'tone' => 'indigo', 'hint' => null, 'trend' => null])

@php
    $tones = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
        'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'],
    ][$tone] ?? ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'];
@endphp

<div class="card p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones['bg'] }} {{ $tones['text'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>

    @if ($hint || $trend)
        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
            @if ($trend)
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                    <x-icon name="trending-up" class="h-3.5 w-3.5" />{{ $trend }}
                </span>
            @endif
            @if ($hint)
                <span>{{ $hint }}</span>
            @endif
        </div>
    @endif
</div>
