<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
        <div class="navbar border-b border-base-300 bg-base-100 px-4 shadow-sm lg:px-8">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3">
                <x-app-logo href="{{ route('home') }}" class="btn btn-ghost gap-2 px-2" wire:navigate />

                <div class="flex items-center gap-2">
                    @auth
                        <button
                            type="button"
                            class="btn btn-ghost btn-square btn-sm"
                            x-data="themeToggle"
                            x-on:click="cycle"
                            x-bind:aria-label="theme === 'dark' ? 'Dark mode' : 'Light mode'"
                            data-test="theme-toggle"
                        >
                            <svg
                                x-show="theme === 'light'"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-5"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            <svg
                                x-show="theme === 'dark'"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-5"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>

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
