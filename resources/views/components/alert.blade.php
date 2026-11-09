@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-800',
        'error' => 'border-rose-500/20 bg-rose-500/10 text-rose-800',
        'warning' => 'border-amber-500/20 bg-amber-500/10 text-amber-800',
        'info' => 'border-sky-500/20 bg-sky-500/10 text-sky-800',
    ][$type] ?? 'border-white/70 bg-white/60 text-slate-700';

    $icons = [
        'success' => 'check',
        'error' => 'alert',
        'warning' => 'alert',
        'info' => 'bell',
    ];
@endphp

<div x-data="{ show: true }" x-show="show" x-cloak
     class="mb-5 flex items-start gap-3 rounded-[22px] border px-4 py-3 text-sm font-medium shadow-[0_14px_36px_-24px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.6)] backdrop-blur-2xl {{ $styles }}">
    <x-icon :name="$icons[$type] ?? 'bell'" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="flex-1">{{ $slot }}</div>
    <button type="button" @click="show = false" class="rounded-full p-0.5 opacity-60 transition hover:bg-white/40 hover:opacity-100">
        <x-icon name="x" class="h-4 w-4" />
    </button>
</div>
