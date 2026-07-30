<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <flux:heading size="xl" class="text-sumi">{{ __('Confirm password') }}</flux:heading>
        <flux:subheading class="mt-1 text-sumi/70">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </flux:subheading>

        <flux:separator class="my-6 border-ai/15" />

        <flux:card class="border-ai/15 bg-washi-light">
            <div class="flex flex-col gap-6">
                <x-auth-session-status class="text-center text-shu" :status="session('status')" />

                <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Password')"
                        viewable
                    />

                    <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                        {{ __('Confirm') }}
                    </flux:button>
                </form>
            </div>
        </flux:card>
    </section>
</x-layouts::public>
