@props([
    'sidebar' => false,
])

<a {{ $attributes->class(['inline-flex items-center gap-2 font-bold', 'text-lg' => ! $sidebar, 'text-base' => $sidebar]) }}>
    <span class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-content">
        <x-app-logo-icon class="size-5 fill-current" />
    </span>
    <span @class(['hidden lg:inline' => $sidebar])>文倉</span>
</a>
