@props([
    'body',
    'tag' => 'div',
])

@php
    $tag = in_array($tag, ['div', 'p', 'span'], true) ? $tag : 'div';
@endphp

<{{ $tag }} {{ $attributes->class(['story-body', 'whitespace-pre-line']) }}>
    {!! \App\Support\Furigana::toHtml($body) !!}
</{{ $tag }}>
