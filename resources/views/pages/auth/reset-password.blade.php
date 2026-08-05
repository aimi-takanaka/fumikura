<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <x-auth-session-status class="text-center text-success" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <label class="form-control w-full">
                <span class="label">
                    <span class="label-text">{{ __('Email') }}</span>
                </span>
                <input
                    name="email"
                    type="email"
                    value="{{ request('email') }}"
                    required
                    autocomplete="email"
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

            <button type="submit" class="btn btn-primary w-full" data-test="reset-password-button">
                {{ __('Reset password') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
