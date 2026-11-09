@props(['title' => 'Belum ada data', 'description' => null, 'icon' => 'archive'])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="flex h-16 w-16 items-center justify-center rounded-[22px] border border-white/70 bg-white/60 text-slate-400 shadow-[0_16px_40px_-24px_rgba(15,23,42,0.4),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-xl">
        <x-icon :name="$icon" class="h-7 w-7" />
    </div>
    <h3 class="mt-4 text-sm font-semibold text-slate-800">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
