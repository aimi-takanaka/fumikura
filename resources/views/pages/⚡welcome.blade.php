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
    <section class="border-b border-ai/15 py-14 text-center">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">Hero</p>

        <h1 class="mb-3.5 text-3xl font-bold text-sumi">
            言葉に詰まったら、絵を見よう。
        </h1>

        <p class="mx-auto mb-5 max-w-[420px] text-base leading-relaxed text-sumi/70">
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

        <div class="overflow-hidden rounded-lg border border-ai/20 bg-washi-light">
            <img
                src="{{ asset('img/logo/banner-header-1500x500.png') }}"
                alt="お題イラストのサンプル"
                class="h-[220px] w-full object-cover"
            />
        </div>
    </section>

    {{-- Topics --}}
    <section id="topics" class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">Topics（未ログインでも閲覧可）</p>

        <h2 class="mb-3 text-lg font-bold text-sumi">お題一覧</h2>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ([
                'latest' => '新着',
                'popular' => '人気',
                'most_completed' => '完成ストーリーが多い順',
            ] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('sort', '{{ $value }}')"
                    @class([
                        'rounded-full border px-3 py-1 text-sm transition',
                        'border-shu bg-shu text-washi-light' => $sort === $value,
                        'border-ai/30 text-ai hover:border-shu/40 hover:text-shu' => $sort !== $value,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
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
                        class="block overflow-hidden rounded-lg border border-ai/15 bg-washi-light transition hover:border-ai/40"
                    >
                        <div class="relative aspect-video overflow-hidden bg-washi">
                            @if ($image->file_path && Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path))
                                <img
                                    src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($image->file_path) }}"
                                    alt="{{ $image->title }}"
                                    class="size-full object-cover"
                                />
                            @else
                                <x-placeholder-pattern class="absolute inset-0 size-full stroke-ai/20" />
                            @endif
                        </div>
                        <div class="px-3 py-3 text-base leading-relaxed text-ai">
                            <div class="truncate font-medium text-sumi">{{ $image->title }}</div>
                            <div class="mt-1">
                                <span class="font-bold text-sumi">完成{{ $image->completed_stories_count }}件</span>
                                ・大喜利{{ $image->draft_stories_count }}件
                            </div>
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
    <section class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">Problem</p>

        <h2 class="mb-4 text-lg font-bold text-sumi">こんな経験、ありませんか？</h2>

        <ul class="grid gap-3">
            @foreach ([
                '書きたい気持ちはあるのに、何を書けばいいかわからない',
                '三題噺のような単語のお題だと、うまく物語に落とし込めない',
                '真っ白なページを前にすると、逆に手が止まってしまう',
            ] as $problem)
                <li class="rounded-lg border border-ai/15 bg-washi-light px-4 py-3 text-base text-sumi/80">
                    {{ $problem }}
                </li>
            @endforeach
        </ul>

        <p class="mt-4 text-center text-sm text-ai">
            ↓ だから、文倉は "絵"からはじめる
        </p>
    </section>

    {{-- Features --}}
    <section class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">Features</p>

        <h2 class="mb-4 text-lg font-bold text-sumi">選ばれる理由</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-ai/15 bg-washi-light px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full border-2 border-shu">
                    <x-app-logo-icon class="size-5" />
                </div>
                <h3 class="mb-2 text-base font-bold text-sumi">言葉より想像しやすい</h3>
                <p class="m-0 text-base leading-relaxed text-sumi/70">絵は物語の空気や感情を先に連れてきてくれる</p>
            </div>

            <div class="rounded-lg border border-ai/15 bg-washi-light px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full border-2 border-shu">
                    <flux:icon.pencil-square variant="micro" class="size-4 text-ai" />
                </div>
                <h3 class="mb-2 text-base font-bold text-sumi">書き出しに迷わない</h3>
                <p class="m-0 text-base leading-relaxed text-sumi/70">目の前の一枚が、そのまま創作のきっかけになる</p>
            </div>

            <div class="rounded-lg border border-ai/15 bg-washi-light px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full border-2 border-shu">
                    <flux:icon.book-open variant="micro" class="size-4 text-ai" />
                </div>
                <h3 class="mb-2 text-base font-bold text-sumi">物語は書く人の数だけ広がる</h3>
                <p class="m-0 text-base leading-relaxed text-sumi/70">同じ絵でも解釈は無限。読むことも創作の刺激になる</p>
            </div>

            <div class="rounded-lg border border-ai/15 bg-washi-light px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full border-2 border-shu">
                    <flux:icon.chat-bubble-left-ellipsis variant="micro" class="size-4 text-ai" />
                </div>
                <h3 class="mb-2 text-base font-bold text-sumi">物語にしなくても参加できる</h3>
                <p class="m-0 text-base leading-relaxed text-sumi/70">ひとことのアイデア投稿だけでもOK。みんなの発想が次の一歩のヒントになる</p>
            </div>
        </div>
    </section>

    {{-- My Page --}}
    <section class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">My Page</p>

        <h2 class="mb-4 text-lg font-bold text-sumi">マイページで、少しずつ育てていける</h2>

        <p class="mb-6 text-base leading-relaxed text-sumi/70">
            投稿したアイデアは、マイページでステータス管理。<br>
            思いついたその日に完成させなくても大丈夫。気が向いたときに、下書き→完成へと進められます。
        </p>

        <div class="flex items-center justify-center">
            <div class="w-[120px] text-center">
                <div class="mx-auto mb-2 flex size-8 items-center justify-center rounded-full bg-ai text-sm text-washi-light">1</div>
                <p class="m-0 text-base leading-relaxed text-sumi/70">
                    アイデア<br><span class="text-ai/60">(大喜利投稿)</span>
                </p>
            </div>

            <div class="px-3 text-lg text-ai/30">›</div>

            <div class="w-[120px] text-center">
                <div class="mx-auto mb-2 flex size-8 items-center justify-center rounded-full bg-sumi text-sm text-washi-light">2</div>
                <p class="m-0 text-base leading-relaxed text-sumi/70">
                    下書き<br><span class="text-ai/60">(非公開)</span>
                </p>
            </div>

            <div class="px-3 text-lg text-ai/30">›</div>

            <div class="w-[120px] text-center">
                <div class="mx-auto mb-2 flex size-8 items-center justify-center rounded-full bg-shu text-sm text-washi-light">3</div>
                <p class="m-0 text-base leading-relaxed text-sumi/70">
                    完成<br><span class="text-ai/60">(公開)</span>
                </p>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">How it works</p>

        <h2 class="mb-6 text-lg font-bold text-sumi">はじめかた</h2>

        <div class="flex items-start justify-between gap-3">
            @foreach (['お題の絵を見る', '物語を書く', '公開してみんなに読んでもらう'] as $step)
                @if (! $loop->first)
                    <div class="pt-1 text-sm text-ai/30">›</div>
                @endif
                <div class="flex-1 text-center">
                    <div class="mx-auto mb-2 flex size-8 items-center justify-center rounded-full bg-ai text-sm text-washi-light">
                        {{ $loop->iteration }}
                    </div>
                    <p class="m-0 text-base text-sumi/70">{{ $step }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Sample --}}
    <section class="border-b border-ai/15 py-8">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">Sample</p>

        <h2 class="mb-4 text-lg font-bold text-sumi">実際に生まれた物語（例）</h2>

        @foreach ([
            ['quote' => '「その日、扉の向こうには知らない誰かの日記が置かれていた——」', 'label' => '同じお題から生まれた物語 ①'],
            ['quote' => '「窓の外の光が、いつもと違う色をしていることに気づいたのは……」', 'label' => '同じお題から生まれた物語 ②'],
        ] as $sample)
            <div @class(['grid grid-cols-[130px_1fr] items-start gap-4 rounded-lg border border-ai/15 bg-washi-light p-4', 'mb-3' => ! $loop->last])>
                <div class="h-[90px] rounded border border-ai/15 bg-washi"></div>
                <div class="text-sm leading-relaxed text-sumi/70">
                    {{ $sample['quote'] }}<br>
                    <span class="mt-2 block text-base text-ai/60">{{ $sample['label'] }}</span>
                </div>
            </div>
        @endforeach
    </section>

    {{-- CTA --}}
    <section class="border-b border-ai/15 py-12 text-center">
        <p class="mb-2.5 text-sm font-bold uppercase tracking-widest text-ai">CTA</p>

        <p class="mb-6 text-xl font-bold text-sumi">一枚の絵から、あなたの物語をはじめよう。</p>

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
    <footer class="flex flex-col gap-2 py-6 text-base text-ai/70 sm:flex-row sm:items-center sm:justify-between">
        <span>© 文倉</span>
        <span>利用規約　プライバシーポリシー　お問い合わせ</span>
    </footer>
</div>
