@props([
    'status',
    'size' => 'sm',
])

<flux:badge :size="$size" :color="$status->badgeColor()">
    {{ $status->label() }}
</flux:badge>
