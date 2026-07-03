<?php

namespace App;

enum StoryStatus: string
{
    case Complete = 'complete';
    case Draft = 'draft';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Complete => '完成',
            self::Draft => '散文・アイデア',
        };
    }
}
