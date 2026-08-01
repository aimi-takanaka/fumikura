<?php

use App\Mail\ContactInquiryMail;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    config(['mail.contact_to' => 'hello@example.com']);
    Mail::fake();
});

it('shows the contact page to guests', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('お問い合わせ')
        ->assertSee('お名前')
        ->assertSee('メールアドレス')
        ->assertSee('件名')
        ->assertSee('お問い合わせ内容');
});

it('links to the contact page from the welcome page footer', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('contact'), false);
});

it('prefills name and email for authenticated users', function () {
    $user = User::factory()->create([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
    ]);

    Livewire::actingAs($user)
        ->test('pages::contact')
        ->assertSet('name', '山田太郎')
        ->assertSet('email', 'taro@example.com');
});

it('shows a preview before sending the inquiry', function () {
    Livewire::test('pages::contact')
        ->set('name', '佐藤花子')
        ->set('email', 'hanako@example.com')
        ->set('subject', '機能について')
        ->set('body', 'ふみの感想について質問があります。')
        ->call('preview')
        ->assertHasNoErrors()
        ->assertSet('previewing', true)
        ->assertSee('送信内容の確認')
        ->assertSee('佐藤花子')
        ->assertSee('hanako@example.com')
        ->assertSee('機能について')
        ->assertSee('ふみの感想について質問があります。')
        ->assertSee('修正する')
        ->assertSee('送信する');

    expect(ContactInquiry::query()->count())->toBe(0);
    Mail::assertNothingSent();
});

it('returns to the form from the preview screen', function () {
    Livewire::test('pages::contact')
        ->set('name', '佐藤花子')
        ->set('email', 'hanako@example.com')
        ->set('subject', '機能について')
        ->set('body', 'ふみの感想について質問があります。')
        ->call('preview')
        ->assertSet('previewing', true)
        ->call('edit')
        ->assertSet('previewing', false)
        ->assertSee('確認画面へ');
});

it('stores the inquiry and sends mail after preview confirmation', function () {
    Livewire::test('pages::contact')
        ->set('name', '佐藤花子')
        ->set('email', 'hanako@example.com')
        ->set('subject', '機能について')
        ->set('body', 'ふみの感想について質問があります。')
        ->call('preview')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('previewing', false);

    $inquiry = ContactInquiry::query()->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->name)->toBe('佐藤花子')
        ->and($inquiry->email)->toBe('hanako@example.com')
        ->and($inquiry->subject)->toBe('機能について')
        ->and($inquiry->body)->toBe('ふみの感想について質問があります。');

    Mail::assertSent(ContactInquiryMail::class, function (ContactInquiryMail $mail) use ($inquiry) {
        return $mail->inquiry->is($inquiry)
            && $mail->hasTo('hello@example.com')
            && $mail->hasReplyTo('hanako@example.com');
    });
});

it('requires all contact form fields before preview', function () {
    Livewire::test('pages::contact')
        ->set('name', '')
        ->set('email', '')
        ->set('subject', '')
        ->set('body', '')
        ->call('preview')
        ->assertHasErrors(['name', 'email', 'subject', 'body'])
        ->assertSet('previewing', false);

    expect(ContactInquiry::query()->count())->toBe(0);
    Mail::assertNothingSent();
});

it('rate limits contact form submissions per ip', function () {
    RateLimiter::clear('contact-inquiry:127.0.0.1');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        Livewire::test('pages::contact')
            ->set('name', "送信者{$attempt}")
            ->set('email', "user{$attempt}@example.com")
            ->set('subject', "件名{$attempt}")
            ->set('body', "本文{$attempt}")
            ->call('preview')
            ->call('send')
            ->assertHasNoErrors();
    }

    Livewire::test('pages::contact')
        ->set('name', '6人目')
        ->set('email', 'sixth@example.com')
        ->set('subject', '6件目')
        ->set('body', '6件目の本文')
        ->call('preview')
        ->call('send')
        ->assertHasErrors(['email']);

    expect(ContactInquiry::query()->count())->toBe(5);
});
