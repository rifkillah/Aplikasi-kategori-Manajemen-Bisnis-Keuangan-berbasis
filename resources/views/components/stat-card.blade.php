@props(['label', 'value', 'icon' => 'chart', 'tone' => 'indigo', 'hint' => null, 'trend' => null])

@php
    $tones = [
        'indigo' => ['bg' => 'bg-indigo-500/15', 'text' => 'text-indigo-600', 'ring' => 'ring-indigo-500/20'],
        'emerald' => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-500/20'],
        'amber' => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-600', 'ring' => 'ring-amber-500/20'],
        'rose' => ['bg' => 'bg-rose-500/15', 'text' => 'text-rose-600', 'ring' => 'ring-rose-500/20'],
        'sky' => ['bg' => 'bg-sky-500/15', 'text' => 'text-sky-600', 'ring' => 'ring-sky-500/20'],
        'slate' => ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-600', 'ring' => 'ring-slate-500/15'],
    ][$tone] ?? ['bg' => 'bg-indigo-500/15', 'text' => 'text-indigo-600', 'ring' => 'ring-indigo-500/20'];
@endphp

<div class="card card-hover p-5" data-reveal>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <div class="icon-pop flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 ring-inset {{ $tones['bg'] }} {{ $tones['text'] }} {{ $tones['ring'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>

    <p class="mt-2 break-words text-2xl font-bold leading-tight tracking-tight text-slate-900">{{ $value }}</p>

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
