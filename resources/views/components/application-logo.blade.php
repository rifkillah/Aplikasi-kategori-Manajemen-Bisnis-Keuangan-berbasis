@props(['class' => 'h-9 w-9'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-[30%] bg-gradient-to-br from-indigo-400 via-indigo-500 to-violet-600 text-white shadow-[0_12px_28px_-10px_rgba(79,70,229,0.9),inset_0_1px_0_rgba(255,255,255,0.35)] ring-1 ring-white/20 '.$class]) }}>
    <svg viewBox="0 0 24 24" fill="none" class="h-2/3 w-2/3" aria-hidden="true">
        <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
        <path d="M8 10.5h8M8 14h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
</div>
