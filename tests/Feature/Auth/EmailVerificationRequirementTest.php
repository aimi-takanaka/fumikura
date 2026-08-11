<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

it('sends a verification email when a user registers', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('redirects unverified users to the verification notice after login', function () {
    $user = User::factory()->unverified()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('verification.notice'));
});

it('redirects unverified users away from protected pages', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertRedirect(route('verification.notice'));
});

it('allows verified users to access protected pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk();
});
