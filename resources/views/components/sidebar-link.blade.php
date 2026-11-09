@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ $active ? 'bg-white/15 text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.18)] ring-1 ring-inset ring-white/15' : 'text-slate-400 hover:bg-white/[0.07] hover:text-white' }}">
    <x-icon :name="$icon" class="h-5 w-5 shrink-0 {{ $active ? 'text-indigo-300' : 'text-slate-500 transition group-hover:text-slate-300' }}" />
    <span class="truncate">{{ $slot }}</span>
    @if ($active)
        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-300 shadow-[0_0_10px_rgba(165,180,252,0.9)]"></span>
    @endif
</a>
