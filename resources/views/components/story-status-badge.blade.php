@props([
    'status',
    'size' => 'sm',
])

@php
    $badgeClass = match ($status) {
        \App\StoryStatus::Complete => 'badge-success',
        \App\StoryStatus::Idea => 'badge-info',
        \App\StoryStatus::Draft => 'badge-ghost',
    };

    $sizeClass = $size === 'sm' ? 'badge-sm' : '';
@endphp

<span {{ $attributes->merge(['class' => trim("badge {$badgeClass} {$sizeClass}")]) }}>
    {{ $status->label() }}
</span>
