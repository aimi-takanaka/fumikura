<?php

namespace App;

enum ImageCreditType: string
{
    case Illustration = 'illustration';
    case Photo = 'photo';

    /**
     * Prefix used in public credit lines (e.g. "イラスト：").
     */
    public function creditPrefix(): string
    {
        return match ($this) {
            self::Illustration => 'イラスト：',
            self::Photo => '写真：',
        };
    }

    /**
     * Human readable label for admin forms.
     */
    public function label(): string
    {
        return match ($this) {
            self::Illustration => 'イラスト',
            self::Photo => '写真',
        };
    }
}
