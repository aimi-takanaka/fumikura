@props([
    'user' => null,
    'size' => 'sm',
])

@php
    $sizeClass = match ($size) {
        'lg' => 'w-12',
        'md' => 'w-10',
        default => 'w-8',
    };
@endphp

@if ($user)
    <div class="avatar">
        <div @class(['rounded-full bg-primary/15', $sizeClass])>
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" />
            @else
                <span class="flex h-full items-center justify-center text-xs font-semibold text-primary">
                    {{ $user->initials() }}
                </span>
            @endif
        </div>
    </div>
@endif
