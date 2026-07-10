<?php

use App\Models\Story;
use App\StoryStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Story $story;

    #[Validate('required|string|max:2000')]
    public string $body = '';

    /**
     * Bind the story, enforcing that drafts require login.
     */
    public function mount(Story $story): void
    {
        abort_if($story->status === StoryStatus::Draft && ! auth()->check(), 403);

        $this->story = $story->load(['user', 'image']);
    }

    /**
     * Toggle the current user's like on this story.
     */
    public function toggleLike(): void
    {
        abort_unless(auth()->check(), 403);

        $existingLike = $this->story->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $this->story->likes()->create([
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Toggle the current user's bookmark on this story.
     */
    public function toggleBookmark(): void
    {
        abort_unless(auth()->check(), 403);

        $existingBookmark = $this->story->bookmarks()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingBookmark) {
            $existingBookmark->delete();
        } else {
            $this->story->bookmarks()->create([
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Post a comment as the logged-in user.
     */
    public function addComment(): void
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate();

        $this->story->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $this->reset('body');

        Flux::toast('コメントを投稿しました。', variant: 'success');
    }

    /**
     * Delete one of the current user's own comments.
     */
    public function deleteComment(int $commentId): void
    {
        $comment = $this->story->comments()->findOrFail($commentId);

        abort_unless($comment->user_id === auth()->id(), 403);

        $comment->delete();

        Flux::toast('コメントを削除しました。', variant: 'success');
    }

    /**
     * Delete the story, allowing only its author.
     */
    public function deleteStory(): void
    {
        abort_unless($this->story->user_id === auth()->id(), 403);

        $image = $this->story->image;

        $this->story->delete();

        session()->flash('status', 'ストーリーを削除しました。');

        $this->redirectRoute('images.show', $image, navigate: true);
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'comments' => $this->story->comments()
                ->with('user')
                ->latest()
                ->get(),
            'likesCount' => $this->story->likes()->count(),
            'isLiked' => auth()->check()
                && $this->story->likes()->where('user_id', auth()->id())->exists(),
            'isBookmarked' => auth()->check()
                && $this->story->bookmarks()->where('user_id', auth()->id())->exists(),
        ];
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:button :href="route('images.show', $story->image)" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-4">
        お題「{{ $story->image->title }}」へ戻る
    </flux:button>

    <article>
        <div class="flex items-start justify-between gap-4">
            <flux:heading size="xl">{{ $story->title }}</flux:heading>

            @if ($story->status === StoryStatus::Complete)
                <flux:badge color="lime">完成</flux:badge>
            @else
                <flux:badge color="zinc">散文・アイデア</flux:badge>
            @endif
        </div>

        <div class="mt-2 flex items-center gap-3">
            <flux:text size="sm">{{ $story->user?->name ?? '不明' }}</flux:text>
            <flux:text size="sm">{{ $story->created_at->format('Y年n月j日') }}</flux:text>

            @if ($story->user_id === auth()->id())
                <div class="ml-auto flex items-center gap-2">
                    <flux:button :href="route('stories.edit', $story)" variant="ghost" size="sm" icon="pencil-square" wire:navigate>
                        編集
                    </flux:button>
                    <flux:button
                        wire:click="deleteStory"
                        wire:confirm="このストーリーを削除しますか？この操作は取り消せません。"
                        variant="ghost"
                        size="sm"
                        icon="trash"
                    >
                        削除
                    </flux:button>
                </div>
            @endif
        </div>

        <flux:separator class="my-6" />

        <div class="leading-relaxed whitespace-pre-line">{{ $story->body }}</div>

        <div class="mt-8 flex items-center gap-4">
            @auth
                <flux:button
                    wire:click="toggleLike"
                    :variant="$isLiked ? 'primary' : 'ghost'"
                    size="sm"
                    icon="heart"
                >
                    {{ $likesCount }}
                </flux:button>
            @else
                <flux:text class="flex items-center gap-1">
                    <flux:icon.heart variant="micro" /> {{ $likesCount }}
                </flux:text>
            @endauth

            <flux:text class="flex items-center gap-1">
                <flux:icon.chat-bubble-oval-left variant="micro" /> {{ $comments->count() }}
            </flux:text>

            @auth
                <flux:button
                    wire:click="toggleBookmark"
                    :variant="$isBookmarked ? 'primary' : 'ghost'"
                    size="sm"
                    icon="bookmark"
                    class="ml-auto"
                >
                    {{ $isBookmarked ? '保存済み' : '保存' }}
                </flux:button>
            @endauth
        </div>
    </article>

    <flux:separator class="my-8" />

    <section>
        <flux:heading size="lg">コメント（{{ $comments->count() }}）</flux:heading>

        @auth
            <form wire:submit="addComment" class="mt-4">
                <flux:textarea
                    wire:model="body"
                    placeholder="このストーリーへの感想を書いてみましょう"
                    rows="3"
                />
                <div class="mt-2 flex justify-end">
                    <flux:button type="submit" variant="primary" size="sm" icon="paper-airplane">
                        コメントする
                    </flux:button>
                </div>
            </form>

            <div class="mt-6 flex flex-col gap-4">
                @forelse ($comments as $comment)
                    <flux:card wire:key="comment-{{ $comment->id }}" class="p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <flux:text class="font-medium">{{ $comment->user?->name ?? '不明' }}</flux:text>
                                    <flux:text size="sm">{{ $comment->created_at->diffForHumans() }}</flux:text>
                                </div>
                                <flux:text class="mt-2 whitespace-pre-line">{{ $comment->body }}</flux:text>
                            </div>

                            @if ($comment->user_id === auth()->id())
                                <flux:button
                                    wire:click="deleteComment({{ $comment->id }})"
                                    wire:confirm="このコメントを削除しますか？"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    inset
                                />
                            @endif
                        </div>
                    </flux:card>
                @empty
                    <flux:text size="sm">まだコメントはありません。最初のコメントを書いてみましょう。</flux:text>
                @endforelse
            </div>
        @else
            <flux:callout icon="lock-closed" class="mt-4">
                <flux:callout.heading>コメントはログインすると読めます</flux:callout.heading>
                <flux:callout.text>
                    <flux:link :href="route('login')" wire:navigate>ログイン</flux:link>
                    すると、コメントの閲覧と投稿ができます。
                </flux:callout.text>
            </flux:callout>
        @endauth
    </section>
</div>
