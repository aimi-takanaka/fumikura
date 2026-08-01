<?php

use App\Mail\ContactInquiryMail;
use App\Models\ContactInquiry;
use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('お問い合わせ')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $body = '';

    public bool $previewing = false;

    /**
     * Prefill the form when the visitor is already signed in.
     */
    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Validate the form and show the confirmation preview.
     */
    public function preview(): void
    {
        $this->validate($this->rules());

        $this->previewing = true;
    }

    /**
     * Return to the editable form from the preview.
     */
    public function edit(): void
    {
        $this->previewing = false;
    }

    /**
     * Validate, store, and email the contact inquiry.
     */
    public function send(): void
    {
        if (! $this->previewing) {
            $this->preview();

            return;
        }

        $rateLimitKey = 'contact-inquiry:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            $this->addError('email', "送信回数の上限に達しました。{$seconds}秒後に再度お試しください。");

            return;
        }

        $validated = $this->validate($this->rules());

        $inquiry = ContactInquiry::query()->create($validated);

        Mail::to(config('mail.contact_to'))->send(new ContactInquiryMail($inquiry));

        RateLimiter::hit($rateLimitKey, 60);

        $this->reset('subject', 'body', 'previewing');

        if (auth()->guest()) {
            $this->reset('name', 'email');
        }

        Flux::toast(variant: 'success', text: 'お問い合わせを送信しました。');
    }

    /**
     * @return array<string, list<string>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}; ?>

<div class="mx-auto max-w-2xl">
    <flux:button :href="route('home')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="mb-6">
        トップへ戻る
    </flux:button>

    <flux:heading size="xl">お問い合わせ</flux:heading>
    <flux:text class="mt-2 text-sumi/70">
        @if ($previewing)
            内容をご確認のうえ、問題なければ送信してください。
        @else
            ご質問やご意見がございましたら、以下のフォームよりご連絡ください。
        @endif
    </flux:text>

    <flux:card class="mt-8 border-ai/15 bg-washi-light">
        @if ($previewing)
            <div class="flex flex-col gap-6">
                <flux:heading size="lg">送信内容の確認</flux:heading>

                <div class="space-y-4">
                    <div>
                        <flux:text class="text-sm text-ai/70">お名前</flux:text>
                        <p class="mt-1 whitespace-pre-wrap text-sumi">{{ $name }}</p>
                    </div>

                    <div>
                        <flux:text class="text-sm text-ai/70">メールアドレス</flux:text>
                        <p class="mt-1 whitespace-pre-wrap text-sumi">{{ $email }}</p>
                    </div>

                    <div>
                        <flux:text class="text-sm text-ai/70">件名</flux:text>
                        <p class="mt-1 whitespace-pre-wrap text-sumi">{{ $subject }}</p>
                    </div>

                    <div>
                        <flux:text class="text-sm text-ai/70">お問い合わせ内容</flux:text>
                        <p class="mt-1 whitespace-pre-wrap text-sumi">{{ $body }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button type="button" variant="ghost" wire:click="edit" data-test="contact-edit-button">
                        修正する
                    </flux:button>
                    <flux:button type="button" variant="primary" wire:click="send" data-test="contact-submit-button">
                        <span wire:loading.remove wire:target="send">送信する</span>
                        <span wire:loading wire:target="send">送信中...</span>
                    </flux:button>
                </div>
            </div>
        @else
            <form wire:submit="preview" class="flex flex-col gap-6">
                <flux:input
                    wire:model="name"
                    label="お名前"
                    type="text"
                    required
                    autocomplete="name"
                />

                <flux:input
                    wire:model="email"
                    label="メールアドレス"
                    type="email"
                    required
                    autocomplete="email"
                />

                <flux:input
                    wire:model="subject"
                    label="件名"
                    type="text"
                    required
                    autocomplete="off"
                />

                <flux:textarea
                    wire:model="body"
                    label="お問い合わせ内容"
                    rows="8"
                    required
                />

                <div class="flex items-center justify-end gap-3">
                    <flux:button type="submit" variant="primary" data-test="contact-preview-button">
                        <span wire:loading.remove wire:target="preview">確認画面へ</span>
                        <span wire:loading wire:target="preview">確認中...</span>
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:card>
</div>
