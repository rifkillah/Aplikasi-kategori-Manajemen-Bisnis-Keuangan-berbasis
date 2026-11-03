@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white shadow-sm ring-1 ring-inset ring-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
    <x-icon :name="$icon" class="h-5 w-5 shrink-0 {{ $active ? 'text-indigo-300' : 'text-slate-500 group-hover:text-slate-300' }}" />
    <span class="truncate">{{ $slot }}</span>
</a>
