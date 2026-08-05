<?php

use App\Concerns\ProfileValidationRules;
use App\Livewire\Concerns\NotifiesUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.public')] #[Title('Profile settings')] class extends Component {
    use NotifiesUser, ProfileValidationRules, WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        if ($this->avatar) {
            if ($user->avatar_path !== null) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $this->avatar->store('avatars', 'public');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->reset('avatar');

        $this->notify(__('Profile updated.'));
    }

    /**
     * Remove the current user's avatar.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($user->avatar_path);

        $user->forceFill(['avatar_path' => null])->save();

        $this->reset('avatar');

        $this->notify(__('Avatar removed.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('topics.index', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Profile settings') }}</h2>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name, email address, and avatar')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="flex items-center gap-4">
                <div class="avatar">
                    <div class="w-16 rounded-full bg-primary/15">
                        @if ($avatar && $avatar->isPreviewable())
                            <img src="{{ $avatar->temporaryUrl() }}" alt="{{ auth()->user()->name }}" />
                        @elseif (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" />
                        @else
                            <span class="flex h-full items-center justify-center text-lg font-semibold text-primary">
                                {{ auth()->user()->initials() }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('Avatar') }}</legend>
                        <input
                            type="file"
                            wire:model="avatar"
                            accept="image/*"
                            class="file-input file-input-bordered w-full max-w-xs"
                        />
                        <p class="label text-sm text-base-content/60">JPEG / PNG などの画像、最大2MBまで。</p>
                        @error('avatar')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    @if (auth()->user()->avatar_path)
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm self-start"
                            wire:click="removeAvatar"
                            wire:confirm="アバター画像を削除しますか？"
                        >
                            {{ __('Remove avatar') }}
                        </button>
                    @endif
                </div>
            </div>

            <div wire:loading wire:target="avatar" class="text-sm text-base-content/60">
                アップロード中...
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('Name') }}</legend>
                <input
                    wire:model="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    class="input input-bordered w-full"
                />
                @error('name')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('Email') }}</legend>
                    <input
                        wire:model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="input input-bordered w-full"
                    />
                    @error('email')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <p class="mt-4 text-sm text-base-content/80">
                            {{ __('Your email address is unverified.') }}
                            <button type="button" class="link link-primary text-sm" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-secondary">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn btn-primary w-full" data-test="update-profile-button">
                    {{ __('Save') }}
                </button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
