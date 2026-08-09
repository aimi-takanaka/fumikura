@props([
    'size' => 'sm',
])

<span {{ $attributes->class(['loading', 'loading-spinner', 'loading-'.$size, 'text-primary']) }} aria-hidden="true"></span>
