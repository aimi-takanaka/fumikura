@props([
    'body',
    'lines' => 3,
])

@php
    use App\Support\Furigana;

    $lineLimit = (int) $lines;
    $plainBody = Furigana::toPlain($body);
    $bodyLines = preg_split('/\r\n|\r|\n/', $plainBody) ?: [];
    $charLimit = $lineLimit === 2 ? 72 : 108;
    $truncated = false;

    if (count($bodyLines) > $lineLimit) {
        $displayBody = implode("\n", array_slice($bodyLines, 0, $lineLimit));
        $truncated = true;
    } else {
        $displayBody = $plainBody;
    }

    if (mb_strlen($displayBody) > $charLimit) {
        $displayBody = mb_substr($displayBody, 0, $charLimit);
        $truncated = true;
    }
@endphp

<p {{ $attributes->merge(['class' => 'story-body whitespace-pre-line']) }}>
    {{ $displayBody }}@if ($truncated)...<span class="font-medium text-secondary">　続きを読む</span>@endif
</p>
