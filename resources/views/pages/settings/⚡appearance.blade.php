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

                <div class="mt-4" x-data="appearanceTheme()" data-test="appearance-theme-picker">
                    <div class="join grid w-full grid-cols-2">
                        <button
                            type="button"
                            class="btn join-item gap-2"
                            x-bind:class="theme === 'light' ? 'btn-primary' : 'btn-outline'"
                            x-on:click="setTheme('light')"
                            data-test="appearance-theme-light"
                            aria-label="ライトモード"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            ライト
                        </button>
                        <button
                            type="button"
                            class="btn join-item gap-2"
                            x-bind:class="theme === 'dark' ? 'btn-primary' : 'btn-outline'"
                            x-on:click="setTheme('dark')"
                            data-test="appearance-theme-dark"
                            aria-label="ダークモード"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                            ダーク
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
