<?php

use App\StoryStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('マイページ')] class extends Component
{
    /**
     * Delete one of the current user's stories.
     */
    public function deleteStory(int $storyId): void
    {
        $story = auth()->user()->stories()->findOrFail($storyId);

        $story->delete();

        Flux::toast('ストーリーを削除しました。', variant: 'success');
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'stories' => auth()->user()->stories()
                ->with('image')
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:heading size="xl">マイページ</flux:heading>
    <flux:text class="mt-1">自分のプロフィールと、投稿したストーリーをまとめて管理できます。</flux:text>

    <flux:separator class="my-6" />

    <section>
        <flux:heading size="lg">プロフィール</flux:heading>
        <flux:card class="mt-4">
            <div class="flex items-center gap-4">
                <flux:avatar
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                />
                <div>
                    <flux:heading>{{ auth()->user()->name }}</flux:heading>
                    <flux:text size="sm">{{ auth()->user()->email }}</flux:text>
                </div>
            </div>
        </flux:card>
    </section>

    <flux:separator class="my-8" />

    <section>
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg">自分の投稿</flux:heading>
            <flux:button :href="route('bookmarks')" variant="ghost" size="sm" icon="bookmark" wire:navigate>
                お気に入り一覧
            </flux:button>
        </div>

        @if ($stories->isEmpty())
            <flux:callout icon="document-text" class="mt-4">
                <flux:callout.heading>まだ投稿がありません</flux:callout.heading>
                <flux:callout.text>お題ページからストーリーを書いてみましょう。</flux:callout.text>
            </flux:callout>
        @else
            <div class="mt-4 flex flex-col gap-4">
                @foreach ($stories as $story)
                    <flux:card wire:key="story-{{ $story->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <a href="{{ route('stories.show', $story) }}" wire:navigate class="min-w-0 transition hover:opacity-80">
                                <div class="flex items-center gap-2">
                                    @if ($story->status === StoryStatus::Complete)
                                        <flux:badge size="sm" color="lime">完成</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">散文・アイデア</flux:badge>
                                    @endif
                                    <flux:text size="sm">{{ $story->created_at->format('Y年n月j日') }}</flux:text>
                                </div>
                                <flux:heading size="lg" class="mt-2 truncate">{{ $story->title }}</flux:heading>
                                <flux:text size="sm" class="mt-1">お題「{{ $story->image->title }}」</flux:text>
                                <flux:text class="mt-2 line-clamp-2">{{ $story->body }}</flux:text>
                            </a>

                            <div class="flex shrink-0 flex-col gap-1">
                                <flux:button
                                    :href="route('stories.edit', $story)"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil-square"
                                    wire:navigate
                                    inset
                                />
                                <flux:button
                                    wire:click="deleteStory({{ $story->id }})"
                                    wire:confirm="このストーリーを削除しますか？この操作は取り消せません。"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    inset
                                />
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    </section>
</div>
