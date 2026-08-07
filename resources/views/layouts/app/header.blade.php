<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
        <div class="drawer">
            <input id="app-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content flex min-h-screen flex-col">
                <div class="navbar border-b border-base-300 bg-base-100 px-4">
                    <div class="mx-auto flex w-full max-w-7xl items-center gap-2">
                        <div class="flex-none lg:hidden">
                            <label for="app-drawer" class="btn btn-square btn-ghost" aria-label="{{ __('Open menu') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </label>
                        </div>

                        <x-app-logo href="{{ route('topics.index') }}" wire:navigate />

                        <div class="flex-1"></div>

                        <div class="hidden items-center gap-1 lg:flex">
                            <a href="#" class="btn btn-ghost btn-sm btn-square" aria-label="{{ __('Search') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-5 w-5 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </a>
                            <a href="https://github.com/laravel/livewire-starter-kit" target="_blank" class="btn btn-ghost btn-sm">
                                {{ __('Repository') }}
                            </a>
                            <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank" class="btn btn-ghost btn-sm">
                                {{ __('Documentation') }}
                            </a>
                        </div>

                        <x-desktop-user-menu class="dropdown-end" />
                    </div>
                </div>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
                    {{ $slot }}
                </main>
            </div>

            <div class="drawer-side z-40 lg:hidden">
                <label for="app-drawer" aria-label="{{ __('Close menu') }}" class="drawer-overlay"></label>
                <aside class="min-h-full w-64 border-e border-base-300 bg-base-100">
                    <div class="border-b border-base-300 p-4">
                        <x-app-logo :sidebar="true" href="{{ route('topics.index') }}" wire:navigate />
                    </div>
                    <ul class="menu px-2 py-4">
                        <li>
                            <a href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                                {{ __('Repository') }}
                            </a>
                        </li>
                        <li>
                            <a href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                                {{ __('Documentation') }}
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>

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
