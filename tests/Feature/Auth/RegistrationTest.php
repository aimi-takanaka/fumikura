<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertSee('文倉')
        ->assertSee(__('Register'));
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('registration validation errors are shown in Japanese', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors([
            'name' => '名前は必須です。',
            'email' => '指定のメールアドレスは既に使用されています。',
            'password',
        ]);

    $passwordErrors = session('errors')->get('password');

    expect(implode(' ', $passwordErrors))
        ->toContain('パスワード')
        ->not->toContain('The password');
});

test('registration requires mixed-case alphanumeric password of at least 8 characters', function () {
    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'rules@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors('password');

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'rules@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])
        ->assertSessionHasErrors('password');

    $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'rules@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertSessionHasNoErrors();
});
