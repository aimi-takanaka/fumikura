<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Concerns\NotifiesUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Security settings')] class extends Component {
    use NotifiesUser, PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->notify(__('Password updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Security settings') }}</h2>

    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('Current password') }}</legend>
                <input
                    wire:model="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="input input-bordered w-full"
                />
                @error('current_password')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('New password') }}</legend>
                <input
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    class="input input-bordered w-full"
                />
                @error('password')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('Confirm password') }}</legend>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    class="input input-bordered w-full"
                />
                @error('password_confirmation')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn btn-primary" data-test="update-password-button">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
