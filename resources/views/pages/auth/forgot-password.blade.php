<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <x-auth-session-status class="text-center text-success" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <label class="form-control w-full">
                <span class="label">
                    <span class="label-text">{{ __('Email address') }}</span>
                </span>
                <input
                    name="email"
                    type="email"
                    required
                    autofocus
                    placeholder="email@example.com"
                    class="input input-bordered w-full @error('email') input-error @enderror"
                />
                @error('email')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="btn btn-primary w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </button>
        </form>

        <div class="space-x-1 text-center text-sm text-base-content/60 rtl:space-x-reverse">
            <span>{{ __('Or, return to') }}</span>
            <a href="{{ route('login') }}" class="link link-primary" wire:navigate>{{ __('log in') }}</a>
        </div>
    </div>
</x-layouts::auth>
