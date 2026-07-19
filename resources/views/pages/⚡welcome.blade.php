<?php

use App\Models\Image;
use App\StoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.public')] class extends Component
{
    use WithPagination;

    private const PER_PAGE = 10;

    public string $sort = 'latest';

    /**
     * Reset pagination when the sort order changes.
     */
    public function updatedSort(): void
    {
        $this->resetPage();
    }

    /**
     * Build the sorted image query for the topic list.
     *
     * @return Builder<Image>
     */
    private function imagesQuery(): Builder
    {
        $query = Image::query()
            ->with('user')
            ->withCount([
                'stories as completed_stories_count' => fn (Builder $query) => $query->complete(),
                'stories as draft_stories_count' => fn (Builder $query) => $query->where('status', StoryStatus::Draft),
                'stories as stories_count',
            ]);

        return match ($this->sort) {
            'popular' => $query->orderByDesc('stories_count')->orderByDesc('id'),
            'most_completed' => $query->orderByDesc('completed_stories_count')->orderByDesc('id'),
            default => $query->latest(),
        };
    }

    /**
     * Data passed to the view.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'images' => $this->imagesQuery()->paginate(self::PER_PAGE),
        ];
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="border-b border-dashed border-zinc-300 py-14 text-center dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">Hero</p>

        <h1 class="mb-3.5 inline-block border border-dashed border-zinc-300 px-3.5 py-3.5 text-3xl font-bold dark:border-zinc-600">
            言葉に詰まったら、絵を見よう。
        </h1>

        <p class="mx-auto mb-5 max-w-[420px] text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
            「書きたい」はあるのに「何を」が出てこない。<br>
            文倉は、単語ではなく一枚の絵をお題にする創作プラットフォームです。
        </p>

        <div class="mb-8">
            @guest
                <flux:button :href="route('register')" variant="primary" wire:navigate>
                    無料ではじめる
                </flux:button>
            @else
                <flux:button href="#topics" variant="primary" icon="photo">
                    お題を見る
                </flux:button>
            @endguest
        </div>

        <div class="flex h-[220px] items-center justify-center overflow-hidden border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800">
            <img
                src="{{ asset('img/logo/banner-header-1500x500.png') }}"
                alt="お題イラストのサンプル"
                class="size-full object-cover"
            />
        </div>
    </section>

    {{-- Topics --}}
    <section id="topics" class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">Topics（未ログインでも閲覧可）</p>

        <h2 class="mb-3 text-lg font-bold">お題一覧</h2>

        <div class="mb-3.5 flex flex-wrap gap-2 text-base text-zinc-500 dark:text-zinc-400">
            <button
                type="button"
                wire:click="$set('sort', 'latest')"
                @class([
                    'rounded-full border px-2.5 py-1 transition',
                    'border-zinc-800 bg-zinc-800 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' => $sort === 'latest',
                    'border-zinc-300 dark:border-zinc-600' => $sort !== 'latest',
                ])
            >
                新着
            </button>
            <button
                type="button"
                wire:click="$set('sort', 'popular')"
                @class([
                    'rounded-full border px-2.5 py-1 transition',
                    'border-zinc-800 bg-zinc-800 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' => $sort === 'popular',
                    'border-zinc-300 dark:border-zinc-600' => $sort !== 'popular',
                ])
            >
                人気
            </button>
            <button
                type="button"
                wire:click="$set('sort', 'most_completed')"
                @class([
                    'rounded-full border px-2.5 py-1 transition',
                    'border-zinc-800 bg-zinc-800 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' => $sort === 'most_completed',
                    'border-zinc-300 dark:border-zinc-600' => $sort !== 'most_completed',
                ])
            >
                完成ストーリーが多い順
            </button>
        </div>

        @if ($images->isEmpty())
            <flux:callout icon="photo">
                <flux:callout.heading>まだお題がありません</flux:callout.heading>
                <flux:callout.text>管理者がお題画像を投稿すると、ここに一覧が表示されます。</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($images as $image)
                    <a
                        href="{{ route('images.show', $image) }}"
                        wire:key="topic-{{ $image->id }}"
                        wire:navigate
                        class="block border border-zinc-300 transition hover:opacity-90 dark:border-zinc-600"
                    >
                        <div class="relative h-20 overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                                <img
                                    src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                    alt="{{ $image->title }}"
                                    class="size-full object-cover"
                                />
                            @else
                                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                            @endif
                        </div>
                        <div class="px-2.5 py-2 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                            {{ $image->title }}<br>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">
                                完成{{ $image->completed_stories_count }}件
                            </span>
                            ・大喜利{{ $image->draft_stories_count }}件
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($images->hasPages())
                <flux:pagination :paginator="$images" class="mt-6" />
            @endif
        @endif
    </section>

    {{-- Problem --}}
    <section class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">Problem</p>

        <h2 class="mb-3.5 text-lg font-bold">こんな経験、ありませんか？</h2>

        <ul class="grid gap-2.5">
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-base text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                書きたい気持ちはあるのに、何を書けばいいかわからない
            </li>
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-base text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                三題噺のような単語のお題だと、うまく物語に落とし込めない
            </li>
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-base text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                真っ白なページを前にすると、逆に手が止まってしまう
            </li>
        </ul>

        <p class="mt-3.5 text-center text-sm text-zinc-500 dark:text-zinc-400">
            ↓ だから、文倉は "絵"からはじめる
        </p>
    </section>

    {{-- Features --}}
    <section class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">Features</p>

        <h2 class="mb-3.5 text-lg font-bold">選ばれる理由</h2>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <x-app-logo-icon class="size-5" />
                </div>
                <h3 class="mb-2 text-base font-bold">言葉より想像しやすい</h3>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    絵は物語の空気や感情を先に連れてきてくれる
                </p>
            </div>

            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <flux:icon.pencil-square variant="micro" class="size-4" />
                </div>
                <h3 class="mb-2 text-base font-bold">書き出しに迷わない</h3>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    目の前の一枚が、そのまま創作のきっかけになる
                </p>
            </div>

            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <flux:icon.book-open variant="micro" class="size-4" />
                </div>
                <h3 class="mb-2 text-base font-bold">物語は書く人の数だけ広がる</h3>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    同じ絵でも解釈は無限。読むことも創作の刺激になる
                </p>
            </div>

            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <flux:icon.chat-bubble-left-ellipsis variant="micro" class="size-4" />
                </div>
                <h3 class="mb-2 text-base font-bold">物語にしなくても参加できる</h3>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    ひとことのアイデア投稿だけでもOK。みんなの発想が次の一歩のヒントになる
                </p>
            </div>
        </div>
    </section>

    {{-- My Page --}}
    <section class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">My Page</p>

        <h2 class="mb-3.5 text-lg font-bold">マイページで、少しずつ育てていける</h2>

        <p class="mb-4 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
            投稿したアイデアは、マイページでステータス管理。<br>
            思いついたその日に完成させなくても大丈夫。気が向いたときに、下書き→完成へと進められます。
        </p>

        <div class="flex items-center justify-center">
            <div class="w-[110px] text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-500 text-sm text-white">
                    1
                </div>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    アイデア<br><span class="text-zinc-400">(大喜利投稿)</span>
                </p>
            </div>

            <div class="px-2.5 text-base text-zinc-300 dark:text-zinc-600">›</div>

            <div class="w-[110px] text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-600 text-sm text-white dark:bg-zinc-400 dark:text-zinc-900">
                    2
                </div>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    下書き<br><span class="text-zinc-400">(非公開)</span>
                </p>
            </div>

            <div class="px-2.5 text-base text-zinc-300 dark:text-zinc-600">›</div>

            <div class="w-[110px] text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900">
                    3
                </div>
                <p class="m-0 text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                    完成<br><span class="text-zinc-400">(公開)</span>
                </p>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">How it works</p>

        <h2 class="mb-4 text-lg font-bold">はじめかた</h2>

        <div class="flex items-start justify-between gap-2.5">
            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900">
                    1
                </div>
                <p class="m-0 text-base text-zinc-500 dark:text-zinc-400">お題の絵を見る</p>
            </div>

            <div class="pt-1 text-sm text-zinc-300 dark:text-zinc-600">›</div>

            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900">
                    2
                </div>
                <p class="m-0 text-base text-zinc-500 dark:text-zinc-400">物語を書く</p>
            </div>

            <div class="pt-1 text-sm text-zinc-300 dark:text-zinc-600">›</div>

            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900">
                    3
                </div>
                <p class="m-0 text-base text-zinc-500 dark:text-zinc-400">公開してみんなに読んでもらう</p>
            </div>
        </div>
    </section>

    {{-- Sample --}}
    <section class="border-b border-dashed border-zinc-300 py-5 dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">Sample</p>

        <h2 class="mb-3.5 text-lg font-bold">実際に生まれた物語（例）</h2>

        <div class="mb-3 grid grid-cols-[130px_1fr] items-start gap-4 border border-zinc-300 p-3.5 dark:border-zinc-600">
            <div class="h-[90px] border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800"></div>
            <div class="text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                「その日、扉の向こうには知らない誰かの日記が置かれていた——」<br>
                <span class="mt-1.5 block text-base text-zinc-400">同じお題から生まれた物語 ①</span>
            </div>
        </div>

        <div class="grid grid-cols-[130px_1fr] items-start gap-4 border border-zinc-300 p-3.5 dark:border-zinc-600">
            <div class="h-[90px] border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800"></div>
            <div class="text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                「窓の外の光が、いつもと違う色をしていることに気づいたのは……」<br>
                <span class="mt-1.5 block text-base text-zinc-400">同じお題から生まれた物語 ②</span>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-b border-dashed border-zinc-300 py-10 text-center dark:border-zinc-600">
        <p class="mb-2.5 text-base text-zinc-400">CTA</p>

        <p class="mb-4 text-xl font-bold">一枚の絵から、あなたの物語をはじめよう。</p>

        @guest
            <flux:button :href="route('register')" variant="primary" wire:navigate>
                無料ではじめる
            </flux:button>
        @else
            <flux:button href="#topics" variant="primary" icon="photo">
                お題を見る
            </flux:button>
        @endguest
    </section>

    {{-- Footer --}}
    <footer class="flex flex-col gap-2 py-4 text-base text-zinc-500 dark:text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
        <span>© 文倉</span>
        <span>利用規約　プライバシーポリシー　お問い合わせ</span>
    </footer>
</div>
