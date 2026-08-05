<?php

use App\Livewire\Concerns\NotifiesUser;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('マイページ')] class extends Component
{
    use NotifiesUser;

    /**
     * Remove one of the current user's bookmarks.
     */
    public function removeBookmark(int $bookmarkId): void
    {
        $bookmark = auth()->user()->bookmarks()->findOrFail($bookmarkId);

        $bookmark->delete();

        $this->notify('お気に入りから外しました。');
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'bookmarks' => auth()->user()->bookmarks()
                ->with(['story.user', 'story.image'])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold">マイページ</h1>
    <p class="mt-1 text-base-content/70">保存したストーリーをここから読み返せます。</p>

    <div class="divider my-6"></div>

    @include('partials.mypage-tabs')

    <div class="mt-6">
        @if ($bookmarks->isEmpty())
            <div role="alert" class="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <div>
                    <h3 class="font-bold">まだお気に入りがありません</h3>
                    <div class="text-sm">ストーリー詳細ページの「保存」ボタンから追加できます。</div>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($bookmarks as $bookmark)
                    <div class="card card-border bg-base-100" wire:key="bookmark-{{ $bookmark->id }}">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-4">
                                <a href="{{ route('stories.show', $bookmark->story) }}" wire:navigate class="min-w-0 transition hover:opacity-80">
                                    <div class="flex items-center gap-2">
                                        <x-story-status-badge :status="$bookmark->story->status" />
                                    </div>
                                    <h3 class="mt-2 truncate text-lg font-bold">{{ $bookmark->story->title }}</h3>
                                    <div class="mt-1 flex items-center gap-2">
                                        <x-user-avatar :user="$bookmark->story->user" />
                                        <span class="text-sm text-base-content/70">
                                            {{ $bookmark->story->user?->name ?? '不明' }}
                                            ・お題「{{ $bookmark->story->image->title }}」
                                        </span>
                                    </div>
                                    <x-topic-thumbnail :image="$bookmark->story->image" />
                                    <x-story-read-more :body="$bookmark->story->body" :lines="2" class="mt-2" />
                                </a>

                                <button
                                    type="button"
                                    wire:click="removeBookmark({{ $bookmark->id }})"
                                    wire:confirm="お気に入りから外しますか？"
                                    class="btn btn-ghost btn-sm btn-square"
                                    title="お気に入りから外す"
                                >
                                    🔖
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
