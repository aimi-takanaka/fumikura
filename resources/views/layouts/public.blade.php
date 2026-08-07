<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
        <div class="navbar border-b border-base-300 bg-base-100 px-4 shadow-sm lg:px-8">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="btn btn-ghost gap-2 px-2 text-lg font-bold" wire:navigate>
                    <x-app-logo-icon class="size-7" />
                    {{ config('app.name', 'Fumikura') }}
                </a>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm"
                        x-data="themeToggle"
                        x-on:click="cycle"
                        x-text="label()"
                        data-test="theme-toggle"
                        aria-label="Toggle theme"
                    ></button>

                    @auth
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.images.create') }}" class="btn btn-ghost btn-sm" wire:navigate>
                                {{ __('Post a topic') }}
                            </a>
                        @endif

                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost gap-2 px-2">
                                <div class="avatar">
                                    <div class="w-8 rounded-full bg-primary/15">
                                        @if (auth()->user()->avatarUrl())
                                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" />
                                        @else
                                            <span class="flex h-full items-center justify-center text-xs font-semibold text-primary">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            </div>
                            <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-64 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                                <li class="menu-title">
                                    <span class="truncate">{{ auth()->user()->email }}</span>
                                </li>
                                <li>
                                    <a href="{{ route('mypage') }}" wire:navigate>{{ __('My Page') }}</a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full text-left" data-test="logout-button">
                                            {{ __('Log out') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" wire:navigate>{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm" wire:navigate>{{ __('Register') }}</a>
                    @endauth
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-8">
            @if (session('status'))
                <div role="alert" class="alert alert-success mb-6">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @include('partials.fumi-encouragement-callout')

            {{ $slot }}
        </main>

        <div
            x-data="toastStack"
            class="toast toast-end toast-bottom z-[100]"
            @notify.window="push($event.detail.type ?? 'info', $event.detail.message ?? '')"
        >
            <template x-for="toast in toasts" :key="toast.id">
                <div class="alert shadow-lg" :class="alertClass(toast.type)">
                    <span x-text="toast.message"></span>
                </div>
            </template>
        </div>
    </body>
</html>
