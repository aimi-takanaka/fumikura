<?php

use Livewire\Component;

new class extends Component {
    public function openDeleteModal(): void
    {
        $this->dispatch('open-delete-user-modal');
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h3 class="text-lg font-semibold">{{ __('Delete account') }}</h3>
        <p class="text-base-content/70">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <button type="button" class="btn btn-error" wire:click="openDeleteModal" data-test="delete-user-button">
        {{ __('Delete account') }}
    </button>

    <livewire:pages::settings.delete-user-modal />
</section>
