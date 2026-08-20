<?php

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Story;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.public')] #[Title('マイページ')] class extends Component
{
    use NotifiesUser;
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

        $this->notify('ストーリーを削除しました。');
    }

    /**
     * Build the filtered story query for the current user.
     *
     * @return LengthAwarePaginator<int, Story>
     */
    private function filteredStories(): LengthAwarePaginator
    {
        $query = auth()->user()->stories()->with('image')->withCount('likes')->latest();

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
    <h1 class="text-2xl font-bold">マイページ</h1>
    <p class="mt-1 text-base-content/70">自分のプロフィールと、投稿したストーリーをまとめて管理できます。</p>

    <div class="divider my-6"></div>

    <section>
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold">プロフィール</h2>
            <a
                href="{{ route('profile.edit') }}"
                class="btn btn-ghost btn-sm"
                wire:navigate
                data-test="mypage-settings-button"
            >
                {{ __('Settings') }}
            </a>
        </div>
        <div class="card card-border bg-base-100 mt-4">
            <div class="card-body flex-row items-center gap-4">
                <div class="avatar">
                    <div class="w-12 rounded-full bg-primary/15">
                        @if (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" />
                        @else
                            <span class="flex h-full items-center justify-center text-sm font-semibold text-primary">
                                {{ auth()->user()->initials() }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="font-bold">{{ auth()->user()->name }}</h3>
                    <p class="text-sm text-base-content/70">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="divider my-8"></div>

    <section>
        <h2 class="text-xl font-bold">表示フォント</h2>
        <p class="mt-1 text-base-content/70">文倉全体の文字の見え方を選べます。選択内容はこの端末に保存されます。</p>
        <div class="card card-border bg-base-100 mt-4">
            <div class="card-body">
                <x-story-body-font-picker layout="list" class="w-full" />
            </div>
        </div>
    </section>

    <div class="divider my-8"></div>

    <section>
        @include('partials.mypage-tabs')

        @if ($hasStories)
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="form-control flex-1">
                    <span class="label">
                        <span class="label-text">キーワードで検索</span>
                    </span>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="タイトル・本文・お題で検索"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="form-control sm:w-48">
                    <span class="label">
                        <span class="label-text">ステータス</span>
                    </span>
                    <select wire:model.live="statusFilter" class="select select-bordered w-full">
                        <option value="all">すべて</option>
                        <option value="complete">完成</option>
                        <option value="idea">アイデア</option>
                        <option value="draft">下書き</option>
                    </select>
                </label>
            </div>
        @endif

        @if (! $hasStories)
            <div role="alert" class="alert mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div>
                    <h3 class="font-bold">まだ投稿がありません</h3>
                    <div class="text-sm">お題ページからストーリーを書いてみましょう。</div>
                </div>
            </div>
        @elseif ($stories->isEmpty())
            <div role="alert" class="alert mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <div>
                    <h3 class="font-bold">条件に合う投稿がありません</h3>
                    <div class="text-sm">検索キーワードやステータスの絞り込みを変えてみてください。</div>
                </div>
            </div>
        @else
            <div class="mt-4 flex flex-col gap-4">
                @foreach ($stories as $story)
                    <div class="card card-border bg-base-100" wire:key="story-{{ $story->id }}">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-4">
                                <a href="{{ route('stories.show', $story) }}" wire:navigate class="min-w-0 transition hover:opacity-80">
                                    <div class="flex items-center gap-2">
                                        <x-story-status-badge :status="$story->status" />
                                        <span class="text-sm text-base-content/70">{{ $story->created_at->format('Y年n月j日') }}</span>
                                    </div>
                                    <h3 class="mt-2 truncate text-lg font-bold">{{ $story->title }}</h3>
                                    <p class="mt-1 text-sm text-base-content/70">お題「{{ $story->image->title }}」</p>
                                    <x-topic-thumbnail :image="$story->image" />
                                    <x-story-read-more :body="$story->body" :lines="2" class="mt-2" />
                                    <div class="mt-2 text-sm text-base-content/70">
                                        <x-story-likes-count :count="$story->likes_count" />
                                    </div>
                                </a>

                                <div class="flex shrink-0 flex-col gap-1">
                                    <a
                                        href="{{ route('stories.edit', $story) }}"
                                        class="btn btn-ghost btn-sm btn-square"
                                        wire:navigate
                                        title="編集"
                                    >
                                        ✎
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="deleteStory({{ $story->id }})"
                                        wire:confirm="このストーリーを削除しますか？この操作は取り消せません。"
                                        class="btn btn-ghost btn-sm btn-square text-error"
                                        title="削除"
                                    >
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                @include('partials.pagination', ['paginator' => $stories])
            </div>
        @endif
    </section>
</div>
