<?php

namespace App\Livewire\Concerns;

trait NotifiesUser
{
    /**
     * Show a DaisyUI toast notification via Alpine.
     */
    protected function notify(string $message, string $type = 'success'): void
    {
        $payload = json_encode([
            'type' => $type,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);

        $this->js(
            'window.dispatchEvent(new CustomEvent("notify", { detail: '.$payload.' }))'
        );
    }
}
