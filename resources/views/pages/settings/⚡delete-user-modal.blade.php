<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public bool $showModal = false;

    public string $password = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        if ($this->getErrorBag()->isNotEmpty()) {
            $this->showModal = true;
        }
    }

    /**
     * Open the delete account confirmation modal.
     */
    #[On('open-delete-user-modal')]
    public function open(): void
    {
        $this->showModal = true;
    }

    /**
     * Close the delete account confirmation modal.
     */
    public function close(): void
    {
        $this->showModal = false;
        $this->reset('password');
        $this->resetValidation();
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    @if ($showModal)
        <dialog class="modal modal-open" open>
            <div class="modal-box max-w-lg">
                <form method="POST" wire:submit="deleteUser" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold">{{ __('Are you sure you want to delete your account?') }}</h3>
                        <p class="mt-2 text-sm text-base-content/70">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>
                    </div>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('Password') }}</legend>
                        <input
                            wire:model="password"
                            type="password"
                            class="input input-bordered w-full"
                            autofocus
                        />
                        @error('password')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="modal-action">
                        <button type="button" class="btn" wire:click="close">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-error" data-test="confirm-delete-user-button">
                            {{ __('Delete account') }}
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" wire:click="close">close</button>
            </form>
        </dialog>
    @endif
</div>
