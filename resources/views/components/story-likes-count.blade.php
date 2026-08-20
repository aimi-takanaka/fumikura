@props([
    'count' => 0,
])

@php
    $likesCount = (int) $count;
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1',
    'aria-label' => 'いいね '.$likesCount.'件',
]) }}>
    <span aria-hidden="true">♡</span>
    <span>{{ $likesCount }}</span>
</span>
