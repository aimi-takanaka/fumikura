@props([
    'status',
    'size' => 'sm',
])

@php
    $badgeClass = match ($status) {
        \App\StoryStatus::Complete => 'badge-success',
        \App\StoryStatus::Idea => 'border-info bg-info text-white',
        \App\StoryStatus::Draft => 'border-zinc-600 bg-zinc-600 text-white',
    };

    $sizeClass = $size === 'sm' ? 'badge-sm' : '';
@endphp

<span {{ $attributes->merge(['class' => trim("badge {$badgeClass} {$sizeClass}")]) }}>
    {{ $status->label() }}
</span>
