@php
    $isPosts = request()->routeIs('mypage');
    $isBookmarks = request()->routeIs('bookmarks');
@endphp

<div role="tablist" class="tabs tabs-bordered" aria-label="マイページ">
    <a
        href="{{ route('mypage') }}"
        wire:navigate
        role="tab"
        @class([
            'tab',
            'tab-active' => $isPosts,
        ])
        @if ($isPosts) aria-current="page" @endif
    >
        自分の投稿
    </a>
    <a
        href="{{ route('bookmarks') }}"
        wire:navigate
        role="tab"
        @class([
            'tab',
            'tab-active' => $isBookmarks,
        ])
        @if ($isBookmarks) aria-current="page" @endif
    >
        お気に入り一覧
    </a>
</div>
