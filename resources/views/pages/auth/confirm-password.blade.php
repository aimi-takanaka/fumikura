<x-layouts::public>
    <section class="mx-auto w-full max-w-lg">
        <h1 class="text-2xl font-bold text-base-content">{{ __('Confirm password') }}</h1>
        <p class="mt-1 text-base-content/70">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>

        <div class="divider my-6"></div>

        <div class="card card-border bg-base-100">
            <div class="card-body flex flex-col gap-6">
                <x-auth-session-status class="text-center text-success" :status="session('status')" />

                <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
                    @csrf

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

                    <button type="submit" class="btn btn-primary w-full" data-test="confirm-password-button">
                        {{ __('Confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts::public>
