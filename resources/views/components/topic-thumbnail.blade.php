@props([
    'image',
])

@if ($image->url())
    <img
        {{ $attributes->merge(['class' => 'mt-2 h-14 w-24 shrink-0 rounded border border-ai/15 object-cover']) }}
        src="{{ $image->url() }}"
        alt="{{ $image->title }}"
    />
@else
    <div {{ $attributes->merge(['class' => 'relative mt-2 h-14 w-24 shrink-0 overflow-hidden rounded border border-ai/15 bg-washi']) }}>
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-ai/20" />
    </div>
@endif
