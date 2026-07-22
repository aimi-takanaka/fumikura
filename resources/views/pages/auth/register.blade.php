<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <flux:heading size="xl" class="text-sumi">{{ __('Register') }}</flux:heading>
        <flux:subheading class="mt-1 text-sumi/70">
            {{ __('Enter your details below to create your account') }}
        </flux:subheading>

        <flux:separator class="my-6 border-ai/15" />

        <flux:card class="border-ai/15 bg-washi-light">
            <div class="flex flex-col gap-6">
                <x-auth-session-status class="text-center text-shu" :status="session('status')" />

                <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <flux:input
                        name="name"
                        :label="__('Name')"
                        :value="old('name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="__('Username')"
                    />

                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Password')"
                        passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        viewable
                    />

                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirm password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Confirm password')"
                        passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        viewable
                    />

                    <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                        {{ __('Create account') }}
                    </flux:button>
                </form>

                <div class="space-x-1 text-center text-sm text-sumi/70 rtl:space-x-reverse">
                    <span>{{ __('Already have an account?') }}</span>
                    <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
                </div>
            </div>
        </flux:card>
    </section>
</x-layouts::public>
