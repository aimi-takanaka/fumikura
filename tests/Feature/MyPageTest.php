<?php

use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $this->get(route('mypage'))
        ->assertRedirect(route('login'));
});

it('shows the current user profile', function () {
    $user = User::factory()->create([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk()
        ->assertSee('山田太郎')
        ->assertSee('taro@example.com');
});

it('shows only the current user\'s stories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '自分の物語']);
    Story::factory()->for($other)->complete()->create(['title' => '他人の物語']);

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk()
        ->assertSee('自分の物語')
        ->assertDontSee('他人の物語');
});

it('shows an empty state when there are no stories', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('mypage'))
        ->assertOk()
        ->assertSee('まだ投稿がありません');
});

it('lets a user delete their own story from the list', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->call('deleteStory', $story->id);

    expect(Story::find($story->id))->toBeNull();
});

it('does not let a user delete someone else\'s story', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $story = Story::factory()->for($other)->create();

    expect(fn () => Livewire::actingAs($user)
        ->test('pages::mypage')
        ->call('deleteStory', $story->id)
    )->toThrow(ModelNotFoundException::class);

    expect(Story::find($story->id))->not->toBeNull();
});

it('shows edit links for each story', function () {
    $user = User::factory()->create();
    $story = Story::factory()->for($user)->complete()->create();

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk()
        ->assertSee(route('stories.edit', $story), false);
});
