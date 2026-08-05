<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="fumikura">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-primary" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
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
