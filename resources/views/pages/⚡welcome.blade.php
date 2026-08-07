<?php

use App\Livewire\Concerns\ListsTopics;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    use ListsTopics;

    /**
     * Logged-in users should land on the topic list, not the marketing page.
     */
    public function mount(): void
    {
        if (! auth()->check()) {
            return;
        }

        if (! auth()->user()->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice', navigate: true);

            return;
        }

        $this->redirectRoute('topics.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return $this->topicListData(6);
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="hero border-b border-base-300 py-14">
        <div class="hero-content flex-col text-center">
            <h1 class="mb-3.5 text-3xl font-bold text-base-content">
                言葉に詰まったら、絵を見よう。
            </h1>

            <p class="mx-auto mb-5 max-w-xl text-base leading-relaxed text-base-content/70">
                「書きたい」はあるのに「何を」が出てこない。<br>
                文倉は、単語ではなく一枚の絵をお題にする創作プラットフォームです。
            </p>

            <div class="mb-8">
                <a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>
                    無料ではじめる
                </a>
            </div>

            <div class="card w-full max-w-4xl overflow-hidden bg-base-100 shadow-sm">
                <figure>
            <img
                src="{{ asset('img/logo/banner-header-1500x500.png') }}"
                alt="お題イラストのサンプル"
                class="w-full object-contain sm:h-[220px] sm:object-cover sm:object-center"
            />
                </figure>
            </div>
        </div>
    </section>

    {{-- Problem --}}
    <section class="border-b border-base-300 py-8">

        <h2 class="mb-4 text-lg font-bold text-base-content">こんな経験、ありませんか？</h2>

        <ul class="grid gap-3">
            @foreach ([
                '書きたい気持ちはあるのに、何を書けばいいかわからない',
                '三題噺のような単語のお題だと、うまく物語に落とし込めない',
                '真っ白なページを前にすると、逆に手が止まってしまう',
            ] as $problem)
                <li class="card card-border bg-base-100 px-4 py-3 text-base text-base-content/80">
                    {{ $problem }}
                </li>
            @endforeach
        </ul>

        <p class="mt-12 mb-8 text-center text-xl font-bold text-base-content">
            ↓ だから、文倉は "絵"からはじめる
        </p>
    </section>

    {{-- Features --}}
    <section class="border-b border-base-300 py-8">

        <h2 class="mb-4 text-lg font-bold text-base-content">選ばれる理由</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="card card-border bg-base-100 px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex h-64 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/features-1.png') }}"
                        alt="言葉より想像しやすい"
                        class="max-h-64 w-auto object-contain"
                    />
                </div>
                <h3 class="mb-2 text-base font-bold text-base-content">言葉より想像しやすい</h3>
                <p class="m-0 text-base leading-relaxed text-base-content/70">絵は物語の空気や感情を先に連れてきてくれる</p>
            </div>

            <div class="card card-border bg-base-100 px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex h-64 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/features-2.png') }}"
                        alt="書き出しに迷わない"
                        class="max-h-64 w-auto object-contain"
                    />
                </div>
                <h3 class="mb-2 text-base font-bold text-base-content">書き出しに迷わない</h3>
                <p class="m-0 text-base leading-relaxed text-base-content/70">目の前の一枚が、そのまま創作のきっかけになる</p>
            </div>

            <div class="card card-border bg-base-100 px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex h-64 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/features-3.png') }}"
                        alt="物語は書く人の数だけ広がる"
                        class="max-h-64 w-auto object-contain"
                    />
                </div>
                <h3 class="mb-2 text-base font-bold text-base-content">物語は書く人の数だけ広がる</h3>
                <p class="m-0 text-base leading-relaxed text-base-content/70">同じ絵でも解釈は無限。読むことも創作の刺激になる</p>
            </div>

            <div class="card card-border bg-base-100 px-4 py-5 text-center">
                <div class="mx-auto mb-3 flex h-64 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/features-4.png') }}"
                        alt="物語にしなくても参加できる"
                        class="max-h-60 w-auto object-contain"
                    />
                </div>
                <h3 class="mb-2 text-base font-bold text-base-content">物語にしなくても参加できる</h3>
                <p class="m-0 text-base leading-relaxed text-base-content/70">ひとことのアイデア投稿だけでもOK。みんなの発想が次の一歩のヒントになる</p>
            </div>
        </div>
    </section>

    {{-- My Page --}}
    <section class="border-b border-base-300 py-8">

        <h2 class="mb-4 text-lg font-bold text-base-content">マイページで、少しずつ育てていける</h2>

        <p class="mb-6 text-base leading-relaxed text-base-content/70">
            投稿したストーリーは、マイページでステータス管理。<br>
            アイデア → 下書き → 完成と、少しずつ育てていけます。
        </p>

        <div class="flex items-start justify-center">
            <div class="w-[140px] text-center">
                <div class="mx-auto mb-3 flex h-36 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/MyPage-1.PNG') }}"
                        alt="アイデア"
                        class="max-h-36 w-auto object-contain"
                    />
                </div>
                <div class="badge badge-primary badge-lg mx-auto mb-2">1</div>
                <p class="m-0 text-base leading-relaxed text-base-content/70">
                    アイデア<br>
                    <span class="text-sm text-base-content/60">(みんなで見れる)</span><br>
                    <span class="text-xs text-base-content/60">※ログイン必須</span>
                </p>
            </div>

            <div class="px-3 text-lg text-base-content/30"></div>

            <div class="w-[140px] text-center">
                <div class="mx-auto mb-3 flex h-36 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/MyPage-2.PNG') }}"
                        alt="下書き"
                        class="max-h-36 w-auto object-contain"
                    />
                </div>
                <div class="badge badge-neutral badge-lg mx-auto mb-2">2</div>
                <p class="m-0 text-base leading-relaxed text-base-content/70">
                    下書き<br>
                    <span class="text-sm text-base-content/60">(本人のみ)</span>
                </p>
            </div>

            <div class="px-3 text-lg text-base-content/30"></div>

            <div class="w-[140px] text-center">
                <div class="mx-auto mb-3 flex h-36 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/MyPage-3.PNG') }}"
                        alt="完成"
                        class="max-h-36 w-auto object-contain"
                    />
                </div>
                <div class="badge badge-secondary badge-lg mx-auto mb-2">3</div>
                <p class="m-0 text-base leading-relaxed text-base-content/70">
                    完成<br>
                    <span class="text-sm text-base-content/60">(誰でも見れる)</span><br>
                    <span class="text-xs text-base-content/60">※ログイン不要</span>
                </p>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-b border-base-300 py-8">

        <h2 class="mb-6 text-lg font-bold text-base-content">はじめかた</h2>

        @php
            $howSteps = [
                ['お題の絵を見る', 'How-1.PNG'],
                ['物語を書く', 'How-2.PNG'],
                ['公開！！', 'How-3.PNG'],
            ];
        @endphp

        <div class="mx-auto grid max-w-xl grid-cols-[1fr_auto_1fr_auto_1fr] items-end gap-x-3">
            @foreach ($howSteps as $index => $step)
                @if ($index > 0)
                    <div class="pb-2 text-center text-lg text-base-content/30">›</div>
                @endif
                <div class="flex h-36 items-end justify-center">
                    <img
                        src="{{ asset('img/welcome/'.$step[1]) }}"
                        alt="{{ $step[0] }}"
                        class="max-h-36 w-auto max-w-full object-contain object-bottom"
                    />
                </div>
            @endforeach

            @foreach ($howSteps as $index => $step)
                @if ($index > 0)
                    <div></div>
                @endif
                <div class="mt-3 flex justify-center">
                    <div class="badge badge-primary badge-lg">
                        {{ $index + 1 }}
                    </div>
                </div>
            @endforeach

            @foreach ($howSteps as $index => $step)
                @if ($index > 0)
                    <div></div>
                @endif
                <p class="mt-2 text-center text-base text-base-content/70">{{ $step[0] }}</p>
            @endforeach
        </div>
    </section>

    {{-- Sample --}}
    <section class="py-8">
        <h2 class="mb-4 text-lg font-bold text-base-content">実際に生まれた物語</h2>
        @include('partials.topic-list', [
            'showAnchor' => true,
            'showHeading' => false,
        ])
    </section>

    {{-- CTA --}}
    <section class="border-b border-base-300 py-12 text-center">
        <p class="mb-6 text-xl font-bold text-base-content">一枚の絵から、あなたの物語をはじめよう。</p>

        <a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>
            無料ではじめる
        </a>
    </section>

    {{-- Footer --}}
    <footer class="flex flex-col gap-2 py-6 text-base text-base-content/70 sm:flex-row sm:items-center sm:justify-between">
        <span>© 文倉</span>
        <div class="flex flex-wrap gap-x-4 gap-y-1">
            <a href="{{ route('terms') }}" class="link link-hover" wire:navigate>利用規約</a>
            <a href="{{ route('privacy') }}" class="link link-hover" wire:navigate>プライバシーポリシー</a>
            <a href="{{ route('contact') }}" class="link link-hover" wire:navigate>お問い合わせ</a>
        </div>
    </footer>
</div>
