<?php

namespace App\Support;

final class Furigana
{
    /**
     * Convert Aozora-style furigana markers into escaped HTML with ruby tags.
     *
     * Supported notations:
     * - 漢字《かんじ》
     * - |言葉《ことば》 or ｜言葉《ことば》
     */
    public static function toHtml(string $text): string
    {
        $pattern = '/(?:[|｜]([^|｜《\r\n]+)|(\p{Han}+))《([^》]+)》/u';

        if (! preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return e($text);
        }

        $html = '';
        $cursor = 0;

        foreach ($matches as $match) {
            $full = $match[0][0];
            $position = $match[0][1];
            $base = ($match[1][0] ?? '') !== '' ? $match[1][0] : ($match[2][0] ?? '');
            $reading = $match[3][0] ?? '';

            $html .= e(substr($text, $cursor, $position - $cursor));
            $html .= '<ruby>'.e($base).'<rp>（</rp><rt>'.e($reading).'</rt><rp>）</rp></ruby>';
            $cursor = $position + strlen($full);
        }

        return $html.e(substr($text, $cursor));
    }

    /**
     * Strip furigana markers, keeping only the base text.
     */
    public static function toPlain(string $text): string
    {
        $pattern = '/(?:[|｜]([^|｜《\r\n]+)|(\p{Han}+))《([^》]+)》/u';

        return (string) preg_replace_callback(
            $pattern,
            fn (array $matches): string => ($matches[1] ?? '') !== '' ? $matches[1] : ($matches[2] ?? ''),
            $text,
        );
    }
}
