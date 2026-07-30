<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <flux:heading size="xl" class="text-sumi">{{ __('Log in') }}</flux:heading>
        <flux:subheading class="mt-1 text-sumi/70">
            {{ __('Enter your email and password below to log in') }}
        </flux:subheading>

        <flux:separator class="my-6 border-ai/15" />

        <flux:card class="border-ai/15 bg-washi-light">
            <div class="flex flex-col gap-6">
                <x-auth-session-status class="text-center text-shu" :status="session('status')" />

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('Password')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                    <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                        {{ __('Log in') }}
                    </flux:button>
                </form>

                <div class="space-x-1 text-center text-sm text-sumi/70 rtl:space-x-reverse">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
                </div>
            </div>
        </flux:card>
    </section>
</x-layouts::public>
