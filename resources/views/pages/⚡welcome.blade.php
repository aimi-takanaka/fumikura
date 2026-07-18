<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
}; ?>

<div class="mx-auto max-w-2xl text-center">
    <flux:heading size="xl">文倉</flux:heading>
    <flux:text class="mt-4">
        一枚の画像から、いくつもの物語が生まれます。お題を選んで、あなただけのストーリーを書いてみましょう。
    </flux:text>

    <div class="mt-8">
        <flux:button :href="route('images.index')" variant="primary" icon="photo" wire:navigate>
            お題一覧を見る
        </flux:button>
    </div>
</div>
