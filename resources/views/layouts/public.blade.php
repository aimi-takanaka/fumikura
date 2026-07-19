<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <header class="border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                    <x-app-logo-icon class="size-7 fill-current text-zinc-900 dark:text-white" />
                    <span class="text-lg font-semibold">文倉</span>
                </a>

                <nav class="flex items-center gap-2">
                    <flux:button href="{{ route('home') }}#topics" variant="ghost" size="sm" icon="photo" wire:navigate>
                        {{ __('お題一覧') }}
                    </flux:button>
                    @auth
                        @if (auth()->user()->is_admin)
                            <flux:button :href="route('admin.images.create')" variant="ghost" size="sm" icon="plus" wire:navigate>
                                {{ __('お題を投稿') }}
                            </flux:button>
                        @endif
                        <flux:button :href="route('mypage')" variant="ghost" size="sm" icon="user" wire:navigate>
                            {{ __('マイページ') }}
                        </flux:button>
                        <flux:button :href="route('bookmarks')" variant="ghost" size="sm" icon="bookmark" wire:navigate>
                            {{ __('お気に入り') }}
                        </flux:button>
                        <flux:button :href="route('dashboard')" variant="ghost" size="sm" icon="layout-grid" wire:navigate>
                            {{ __('ダッシュボード') }}
                        </flux:button>

                        <flux:dropdown position="bottom" align="end">
                            <flux:profile
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                                icon:trailing="chevrons-up-down"
                            />

                            <flux:menu>
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->name"
                                        :initials="auth()->user()->initials()"
                                    />
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                                <flux:menu.separator />
                                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                    {{ __('Settings') }}
                                </flux:menu.item>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item
                                        as="button"
                                        type="submit"
                                        icon="arrow-right-start-on-rectangle"
                                        class="w-full cursor-pointer"
                                        data-test="logout-button"
                                    >
                                        {{ __('Log out') }}
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>
                            {{ __('ログイン') }}
                        </flux:button>
                        <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>
                            {{ __('新規登録') }}
                        </flux:button>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">
            @if (session('status'))
                <flux:callout variant="success" icon="check-circle" class="mb-6" dismissible>
                    <flux:callout.text>{{ session('status') }}</flux:callout.text>
                </flux:callout>
            @endif

            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
