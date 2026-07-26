@php
    $isPosts = request()->routeIs('mypage');
    $isBookmarks = request()->routeIs('bookmarks');
@endphp

<nav class="-mb-px flex gap-1 border-b border-ai/15" aria-label="マイページ">
    <a
        href="{{ route('mypage') }}"
        wire:navigate
        @class([
            'border-b-2 px-4 py-2.5 text-sm font-medium transition',
            'border-shu text-shu' => $isPosts,
            'border-transparent text-ai hover:border-ai/30 hover:text-shu' => ! $isPosts,
        ])
        @if ($isPosts) aria-current="page" @endif
    >
        自分の投稿
    </a>
    <a
        href="{{ route('bookmarks') }}"
        wire:navigate
        @class([
            'border-b-2 px-4 py-2.5 text-sm font-medium transition',
            'border-shu text-shu' => $isBookmarks,
            'border-transparent text-ai hover:border-ai/30 hover:text-shu' => ! $isBookmarks,
        ])
        @if ($isBookmarks) aria-current="page" @endif
    >
        保存済み
    </a>
</nav>
