@props([
    'body',
    'lines' => 3,
])

@php
    $lineLimit = (int) $lines;
    $bodyLines = preg_split('/\r\n|\r|\n/', $body);
    $charLimit = $lineLimit === 2 ? 72 : 108;
    $truncated = false;

    if (count($bodyLines) > $lineLimit) {
        $displayBody = implode("\n", array_slice($bodyLines, 0, $lineLimit));
        $truncated = true;
    } else {
        $displayBody = $body;
    }

    if (mb_strlen($displayBody) > $charLimit) {
        $displayBody = mb_substr($displayBody, 0, $charLimit);
        $truncated = true;
    }
@endphp

<flux:text {{ $attributes->merge(['class' => 'story-body whitespace-pre-line']) }}>
    {{ $displayBody }}@if ($truncated)...<span class="font-medium text-shu">　続きを読む</span>@endif
</flux:text>
