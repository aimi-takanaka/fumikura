<?php

use App\Models\Story;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.public')] #[Title('マイページ')] class extends Component
{
    use WithPagination;

    private const PER_PAGE = 10;

    public string $search = '';

    public string $statusFilter = 'all';

    /**
     * Reset pagination when the search query changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when the status filter changes.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

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
     * Build the filtered story query for the current user.
     *
     * @return LengthAwarePaginator<int, Story>
     */
    private function filteredStories(): LengthAwarePaginator
    {
        $query = auth()->user()->stories()->with('image')->latest();

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', $search)
                    ->orWhere('body', 'like', $search)
                    ->orWhereHas('image', fn ($query) => $query->where('title', 'like', $search));
            });
        }

        return $query->paginate(self::PER_PAGE);
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'hasStories' => auth()->user()->stories()->exists(),
            'stories' => $this->filteredStories(),
        ];
    }
}; ?>

<div>
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
                    :src="auth()->user()->avatarUrl()"
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
        @include('partials.mypage-tabs')

        @if ($hasStories)
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <flux:field class="flex-1">
                    <flux:label>キーワードで検索</flux:label>
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="タイトル・本文・お題で検索"
                        icon="magnifying-glass"
                        clearable
                    />
                </flux:field>

                <flux:field class="sm:w-48">
                    <flux:label>ステータス</flux:label>
                    <flux:select wire:model.live="statusFilter">
                        <flux:select.option value="all">すべて</flux:select.option>
                        <flux:select.option value="complete">完成</flux:select.option>
                        <flux:select.option value="idea">アイデア</flux:select.option>
                        <flux:select.option value="draft">下書き</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        @endif

        @if (! $hasStories)
            <flux:callout icon="document-text" class="mt-4">
                <flux:callout.heading>まだ投稿がありません</flux:callout.heading>
                <flux:callout.text>お題ページからストーリーを書いてみましょう。</flux:callout.text>
            </flux:callout>
        @elseif ($stories->isEmpty())
            <flux:callout icon="funnel" class="mt-4">
                <flux:callout.heading>条件に合う投稿がありません</flux:callout.heading>
                <flux:callout.text>検索キーワードやステータスの絞り込みを変えてみてください。</flux:callout.text>
            </flux:callout>
        @else
            <div class="mt-4 flex flex-col gap-4">
                @foreach ($stories as $story)
                    <flux:card wire:key="story-{{ $story->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <a href="{{ route('stories.show', $story) }}" wire:navigate class="min-w-0 transition hover:opacity-80">
                                <div class="flex items-center gap-2">
                                    <x-story-status-badge :status="$story->status" />
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

            @if ($stories->hasPages())
                <flux:pagination :paginator="$stories" class="mt-6" />
            @endif
        @endif
    </section>
</div>
