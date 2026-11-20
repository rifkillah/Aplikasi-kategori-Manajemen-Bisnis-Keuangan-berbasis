@props([
    'user',
    'size' => 'h-9 w-9',
    'text' => 'text-xs',
    'rounded' => 'rounded-full',
])

@if ($user->avatar_url)
    <img src="{{ $user->avatar_url }}"
         alt="Foto {{ $user->name }}"
         {{ $attributes->merge(['class' => "$size $rounded shrink-0 object-cover ring-1 ring-white/25"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$size $rounded flex shrink-0 items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 $text font-bold text-white ring-1 ring-white/20"]) }}>
        {{ $user->initials }}
    </span>
@endif
