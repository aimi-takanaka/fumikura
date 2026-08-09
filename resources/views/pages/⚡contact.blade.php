<?php

use App\Livewire\Concerns\NotifiesUser;
use App\Mail\ContactInquiryMail;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('お問い合わせ')] class extends Component
{
    use NotifiesUser;

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

        $this->notify('お問い合わせを送信しました。');
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
    <a href="{{ route('home') }}" class="btn btn-ghost btn-sm mb-6" wire:navigate>
        ← トップへ戻る
    </a>

    <h1 class="text-2xl font-bold">お問い合わせ</h1>
    <p class="mt-2 text-base-content/70">
        @if ($previewing)
            内容をご確認のうえ、問題なければ送信してください。
        @else
            ご質問やご意見がございましたら、以下のフォームよりご連絡ください。
        @endif
    </p>

    <div class="card card-border bg-base-100 mt-8">
        <div class="card-body">
            @if ($previewing)
                <div class="flex flex-col gap-6">
                    <h2 class="text-lg font-bold">送信内容の確認</h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-base-content/60">お名前</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-base-content/60">メールアドレス</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $email }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-base-content/60">件名</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $subject }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-base-content/60">お問い合わせ内容</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $body }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="btn btn-ghost" wire:click="edit" data-test="contact-edit-button">
                            修正する
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="send"
                            wire:loading.attr="disabled"
                            wire:target="send"
                            data-test="contact-submit-button"
                        >
                            <span wire:loading.remove wire:target="send">送信する</span>
                            <span wire:loading wire:target="send" class="inline-flex items-center gap-2">
                                <x-loading-spinner size="sm" class="text-primary-content" />
                                送信中...
                            </span>
                        </button>
                    </div>
                </div>
            @else
                <form wire:submit="preview" class="flex flex-col gap-6">
                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">お名前</span>
                        </span>
                        <input
                            type="text"
                            wire:model="name"
                            class="input input-bordered w-full @error('name') input-error @enderror"
                            required
                            autocomplete="name"
                        />
                        @error('name')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">メールアドレス</span>
                        </span>
                        <input
                            type="email"
                            wire:model="email"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                            required
                            autocomplete="email"
                        />
                        @error('email')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">件名</span>
                        </span>
                        <input
                            type="text"
                            wire:model="subject"
                            class="input input-bordered w-full @error('subject') input-error @enderror"
                            required
                            autocomplete="off"
                        />
                        @error('subject')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <span class="label">
                            <span class="label-text">お問い合わせ内容</span>
                        </span>
                        <textarea
                            wire:model="body"
                            rows="8"
                            class="textarea textarea-bordered w-full @error('body') textarea-error @enderror"
                            required
                        ></textarea>
                        @error('body')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="preview"
                            data-test="contact-preview-button"
                        >
                            <span wire:loading.remove wire:target="preview">確認画面へ</span>
                            <span wire:loading wire:target="preview" class="inline-flex items-center gap-2">
                                <x-loading-spinner size="sm" class="text-primary-content" />
                                確認中...
                            </span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
