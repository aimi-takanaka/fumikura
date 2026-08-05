<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <h1 class="text-2xl font-bold text-base-content">{{ __('Log in') }}</h1>
        <p class="mt-1 text-base-content/70">
            {{ __('Enter your email and password below to log in') }}
        </p>

        <div class="divider my-6"></div>

        <div class="card card-border bg-base-100">
            <div class="card-body flex flex-col gap-6">
                <x-auth-session-status class="text-center text-success" :status="session('status')" />

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">{{ __('Email address') }}</span>
                        </span>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                        />
                        @error('email')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="relative">
                        <label class="form-control w-full">
                            <span class="label">
                                <span class="label-text">{{ __('Password') }}</span>
                            </span>
                            <input
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('Password') }}"
                                class="input input-bordered w-full @error('password') input-error @enderror"
                            />
                            @error('password')
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        @if (Route::has('password.request'))
                            <a class="link link-hover absolute top-0 text-sm end-0" href="{{ route('password.request') }}" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary" @checked(old('remember')) />
                        <span class="label-text">{{ __('Remember me') }}</span>
                    </label>

                    <button type="submit" class="btn btn-primary w-full" data-test="login-button">
                        {{ __('Log in') }}
                    </button>
                </form>

                <div class="space-x-1 text-center text-sm text-base-content/70 rtl:space-x-reverse">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <a href="{{ route('register') }}" class="link link-primary" wire:navigate>{{ __('Sign up') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
