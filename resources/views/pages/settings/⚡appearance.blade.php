<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Appearance settings') }}</h2>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="space-y-8">
            <div>
                <h3 class="text-base font-semibold">{{ __('Theme') }}</h3>
                <p class="mt-1 text-sm text-base-content/70">{{ __('Choose how the interface looks on this device.') }}</p>

                <div class="mt-4" x-data="appearanceTheme()">
                    <div class="join grid w-full grid-cols-3">
                        <button
                            type="button"
                            class="btn join-item"
                            x-bind:class="theme === 'light' ? 'btn-primary' : 'btn-outline'"
                            x-on:click="setTheme('light')"
                        >
                            {{ __('Light') }}
                        </button>
                        <button
                            type="button"
                            class="btn join-item"
                            x-bind:class="theme === 'dark' ? 'btn-primary' : 'btn-outline'"
                            x-on:click="setTheme('dark')"
                        >
                            {{ __('Dark') }}
                        </button>
                        <button
                            type="button"
                            class="btn join-item"
                            x-bind:class="theme === 'system' ? 'btn-primary' : 'btn-outline'"
                            x-on:click="setTheme('system')"
                        >
                            {{ __('System') }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-base font-semibold">表示フォント</h3>
                <p class="mt-1 text-sm text-base-content/70">文倉全体の文字の見え方を選べます。選択内容はこの端末に保存されます。</p>

                <div class="card mt-4 border border-base-300 bg-base-100">
                    <div class="card-body">
                        <x-story-body-font-picker layout="list" class="w-full" />
                    </div>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
