@props(['tone' => 'gray'])

@php
    $class = match ($tone) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-gray',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>
