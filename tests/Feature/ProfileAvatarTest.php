<?php

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a user can upload an avatar on the profile page', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg'))
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('a user can remove their avatar on the profile page', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar_path' => 'avatars/existing.jpg',
    ]);

    Storage::disk('public')->put('avatars/existing.jpg', 'avatar-content');

    Livewire::actingAs($user)
        ->test('pages::settings.profile')
        ->call('removeAvatar')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/existing.jpg');
});

test('story pages show the author avatar next to their name', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'name' => 'アバター太郎',
        'avatar_path' => 'avatars/taro.jpg',
    ]);

    Storage::disk('public')->put('avatars/taro.jpg', 'avatar-content');

    $story = Story::factory()->for($user)->complete()->create(['title' => 'アバター付き物語']);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('アバター太郎')
        ->assertSee($user->avatarUrl(), false);
});

test('story pages show commenter avatars next to their names', function () {
    Storage::fake('public');

    $commenter = User::factory()->create([
        'name' => 'コメント花子',
        'avatar_path' => 'avatars/hanako.jpg',
    ]);

    Storage::disk('public')->put('avatars/hanako.jpg', 'avatar-content');

    $story = Story::factory()->complete()->create();
    $story->comments()->create([
        'user_id' => $commenter->id,
        'body' => '素敵な物語ですね。',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('コメント花子')
        ->assertSee($commenter->avatarUrl(), false);
});

test('users without an avatar still show their initials', function () {
    $user = User::factory()->create(['name' => '山田太郎']);

    expect($user->avatarUrl())->toBeNull()
        ->and($user->initials())->not->toBe('');
});
