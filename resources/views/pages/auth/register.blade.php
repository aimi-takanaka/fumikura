<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <h1 class="text-2xl font-bold text-base-content">{{ __('Register') }}</h1>
        <p class="mt-1 text-base-content/70">
            {{ __('Enter your details below to create your account') }}
        </p>

        <div class="divider my-6"></div>

        <div class="card card-border bg-base-100">
            <div class="card-body flex flex-col gap-6">
                <x-auth-session-status class="text-center text-success" :status="session('status')" />

                <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">{{ __('Name') }}</span>
                        </span>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="{{ __('Username') }}"
                            class="input input-bordered w-full @error('name') input-error @enderror"
                        />
                        @error('name')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">{{ __('Email address') }}</span>
                        </span>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                        />
                        @error('email')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">{{ __('Password') }}</span>
                        </span>
                        <input
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Password') }}"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            class="input input-bordered w-full @error('password') input-error @enderror"
                        />
                        @error('password')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">{{ __('Confirm password') }}</span>
                        </span>
                        <input
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Confirm password') }}"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            class="input input-bordered w-full"
                        />
                    </label>

                    <button type="submit" class="btn btn-primary w-full" data-test="register-user-button">
                        {{ __('Create account') }}
                    </button>
                </form>

                <div class="space-x-1 text-center text-sm text-base-content/70 rtl:space-x-reverse">
                    <span>{{ __('Already have an account?') }}</span>
                    <a href="{{ route('login') }}" class="link link-primary" wire:navigate>{{ __('Log in') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
