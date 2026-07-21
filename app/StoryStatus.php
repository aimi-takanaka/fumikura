<?php

namespace App;

enum StoryStatus: string
{
    case Complete = 'complete';
    case Idea = 'idea';
    case Draft = 'draft';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Complete => '完成',
            self::Idea => 'アイデア',
            self::Draft => '下書き',
        };
    }

    /**
     * Label shown on create and edit forms.
     */
    public function formLabel(): string
    {
        return match ($this) {
            self::Complete => '完成（誰でも読めます）',
            self::Idea => 'アイデア（ログイン済みの人なら誰でも読めます）',
            self::Draft => '下書き（本人のみ閲覧できます）',
        };
    }

    /**
     * Flux badge color for the status.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Complete => 'lime',
            self::Idea => 'sky',
            self::Draft => 'zinc',
        };
    }
}
