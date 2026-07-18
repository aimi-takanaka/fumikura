<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
}; ?>

<div class="mx-auto max-w-[780px] border border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Hero --}}
    <section class="border-b border-dashed border-zinc-300 px-6 py-14 text-center dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Hero</p>

        <h1 class="mb-3.5 inline-block border border-dashed border-zinc-300 px-3.5 py-3.5 text-2xl font-bold dark:border-zinc-600">
            言葉に詰まったら、絵を見よう。
        </h1>

        <p class="mx-auto mb-5 max-w-[420px] text-[12.5px] leading-relaxed text-zinc-500 dark:text-zinc-400">
            「書きたい」はあるのに「何を」が出てこない。<br>
            文倉は、単語ではなく一枚の絵をお題にする創作プラットフォームです。
        </p>

        <div class="mb-8">
            @guest
                <flux:button :href="route('register')" variant="primary" wire:navigate>
                    無料ではじめる
                </flux:button>
            @else
                <flux:button :href="route('images.index')" variant="primary" icon="photo" wire:navigate>
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

    {{-- Problem --}}
    <section class="border-b border-dashed border-zinc-300 px-6 py-5 dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Problem</p>

        <h2 class="mb-3.5 text-[15px] font-bold">こんな経験、ありませんか？</h2>

        <ul class="grid gap-2.5">
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-[12.5px] text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                書きたい気持ちはあるのに、何を書けばいいかわからない
            </li>
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-[12.5px] text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                三題噺のような単語のお題だと、うまく物語に落とし込めない
            </li>
            <li class="rounded border border-zinc-300 bg-zinc-100 px-4 py-3 text-[12.5px] text-zinc-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                真っ白なページを前にすると、逆に手が止まってしまう
            </li>
        </ul>

        <p class="mt-3.5 text-center text-[11px] text-zinc-500 dark:text-zinc-400">
            ↓ だから、文倉は "絵"からはじめる
        </p>
    </section>

    {{-- Features --}}
    <section class="border-b border-dashed border-zinc-300 px-6 py-5 dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Features</p>

        <h2 class="mb-3.5 text-[15px] font-bold">選ばれる理由</h2>

        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-3">
            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <x-app-logo-icon class="size-5" />
                </div>
                <h3 class="mb-2 text-[12.5px] font-bold">言葉より想像しやすい</h3>
                <p class="m-0 text-[10.5px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                    絵は物語の空気や感情を先に連れてきてくれる
                </p>
            </div>

            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <flux:icon.pencil-square variant="micro" class="size-4" />
                </div>
                <h3 class="mb-2 text-[12.5px] font-bold">書き出しに迷わない</h3>
                <p class="m-0 text-[10.5px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                    目の前の一枚が、そのまま創作のきっかけになる
                </p>
            </div>

            <div class="border border-zinc-300 px-3 py-4 text-center dark:border-zinc-600">
                <div class="mx-auto mb-2.5 flex size-9 items-center justify-center rounded-full border border-zinc-800 dark:border-zinc-300">
                    <flux:icon.book-open variant="micro" class="size-4" />
                </div>
                <h3 class="mb-2 text-[12.5px] font-bold">物語は書く人の数だけ広がる</h3>
                <p class="m-0 text-[10.5px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                    同じ絵でも解釈は無限。読むことも創作の刺激になる
                </p>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-b border-dashed border-zinc-300 px-6 py-5 dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">How it works</p>

        <h2 class="mb-4 text-[15px] font-bold">はじめかた</h2>

        <div class="flex items-start justify-between gap-2.5">
            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-[11px] text-white dark:bg-zinc-100 dark:text-zinc-900">
                    1
                </div>
                <p class="m-0 text-[10.5px] text-zinc-500 dark:text-zinc-400">お題の絵を見る</p>
            </div>

            <div class="pt-1 text-sm text-zinc-300 dark:text-zinc-600">›</div>

            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-[11px] text-white dark:bg-zinc-100 dark:text-zinc-900">
                    2
                </div>
                <p class="m-0 text-[10.5px] text-zinc-500 dark:text-zinc-400">物語を書く</p>
            </div>

            <div class="pt-1 text-sm text-zinc-300 dark:text-zinc-600">›</div>

            <div class="flex-1 text-center">
                <div class="mx-auto mb-2 flex size-[26px] items-center justify-center rounded-full bg-zinc-800 text-[11px] text-white dark:bg-zinc-100 dark:text-zinc-900">
                    3
                </div>
                <p class="m-0 text-[10.5px] text-zinc-500 dark:text-zinc-400">公開してみんなに読んでもらう</p>
            </div>
        </div>
    </section>

    {{-- Sample --}}
    <section class="border-b border-dashed border-zinc-300 px-6 py-5 dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Sample</p>

        <h2 class="mb-3.5 text-[15px] font-bold">実際に生まれた物語（例）</h2>

        <div class="mb-3 grid grid-cols-[130px_1fr] items-start gap-4 border border-zinc-300 p-3.5 dark:border-zinc-600">
            <div class="h-[90px] border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800"></div>
            <div class="text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                「その日、扉の向こうには知らない誰かの日記が置かれていた——」<br>
                <span class="mt-1.5 block text-[10.5px] text-zinc-400">同じお題から生まれた物語 ①</span>
            </div>
        </div>

        <div class="grid grid-cols-[130px_1fr] items-start gap-4 border border-zinc-300 p-3.5 dark:border-zinc-600">
            <div class="h-[90px] border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800"></div>
            <div class="text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                「窓の外の光が、いつもと違う色をしていることに気づいたのは……」<br>
                <span class="mt-1.5 block text-[10.5px] text-zinc-400">同じお題から生まれた物語 ②</span>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-b border-dashed border-zinc-300 px-6 py-10 text-center dark:border-zinc-600 sm:px-[26px]">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-zinc-400">CTA</p>

        <p class="mb-4 text-[17px] font-bold">一枚の絵から、あなたの物語をはじめよう。</p>

        @guest
            <flux:button :href="route('register')" variant="primary" wire:navigate>
                無料ではじめる
            </flux:button>
        @else
            <flux:button :href="route('images.index')" variant="primary" icon="photo" wire:navigate>
                お題を見る
            </flux:button>
        @endguest
    </section>

    {{-- Footer --}}
    <footer class="flex flex-col gap-2 px-6 py-4 text-[10.5px] text-zinc-500 dark:text-zinc-400 sm:flex-row sm:items-center sm:justify-between sm:px-[26px]">
        <span>© 文倉</span>
        <span>利用規約　プライバシーポリシー　お問い合わせ</span>
    </footer>
</div>
