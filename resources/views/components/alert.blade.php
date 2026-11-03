@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800',
    ][$type] ?? 'border-slate-200 bg-slate-50 text-slate-700';

    $icons = [
        'success' => 'check',
        'error' => 'alert',
        'warning' => 'alert',
        'info' => 'bell',
    ];
@endphp

<div x-data="{ show: true }" x-show="show" x-cloak
     class="mb-5 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-medium shadow-sm {{ $styles }}">
    <x-icon :name="$icons[$type] ?? 'bell'" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="flex-1">{{ $slot }}</div>
    <button type="button" @click="show = false" class="rounded-lg p-0.5 opacity-60 transition hover:opacity-100">
        <x-icon name="x" class="h-4 w-4" />
    </button>
</div>
