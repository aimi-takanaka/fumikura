<?php

namespace App;

enum ImageCreditType: string
{
    case Illustration = 'illustration';
    case Photo = 'photo';

    /**
     * Prefix used in public credit lines (e.g. "illustration by").
     */
    public function creditPrefix(): string
    {
        return match ($this) {
            self::Illustration => 'illustration by',
            self::Photo => 'photo by',
        };
    }

    /**
     * Human readable label for admin forms.
     */
    public function label(): string
    {
        return match ($this) {
            self::Illustration => '絵（illustration）',
            self::Photo => '写真（photo）',
        };
    }
}
