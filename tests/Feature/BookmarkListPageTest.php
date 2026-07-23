<?php

use App\Models\Bookmark;
use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $this->get(route('bookmarks'))
        ->assertRedirect(route('login'));
});

it('shows only the current user\'s bookmarked stories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Story::factory()->complete()->create(['title' => '自分が保存した話']);
    $theirs = Story::factory()->complete()->create(['title' => '他人が保存した話']);

    Bookmark::factory()->for($mine)->for($user)->create();
    Bookmark::factory()->for($theirs)->for($other)->create();

    $this->actingAs($user)
        ->get(route('bookmarks'))
        ->assertOk()
        ->assertSee('自分が保存した話')
        ->assertDontSee('他人が保存した話')
        ->assertSee('自分の投稿')
        ->assertSee('お気に入り一覧')
        ->assertSee(route('mypage'), false);
});

it('shows the topic thumbnail below the topic title on bookmarked stories', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $image = Image::factory()->create([
        'title' => '森のお題',
        'file_path' => 'images/forest.jpg',
    ]);

    Storage::disk('public')->put('images/forest.jpg', 'image-content');

    $story = Story::factory()->for($image)->complete()->create(['title' => '森の物語']);
    Bookmark::factory()->for($story)->for($user)->create();

    $this->actingAs($user)
        ->get(route('bookmarks'))
        ->assertOk()
        ->assertSee('森のお題')
        ->assertSee($image->url(), false);
});

it('shows an empty state when there are no bookmarks', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('bookmarks'))
        ->assertOk()
        ->assertSee('まだお気に入りがありません');
});

it('lets a user remove a bookmark from the list', function () {
    $user = User::factory()->create();
    $story = Story::factory()->complete()->create();
    $bookmark = Bookmark::factory()->for($story)->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::bookmarks')
        ->call('removeBookmark', $bookmark->id);

    expect(Bookmark::find($bookmark->id))->toBeNull();
});

it('does not let a user remove someone else\'s bookmark', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $story = Story::factory()->complete()->create();
    $bookmark = Bookmark::factory()->for($story)->for($other)->create();

    expect(fn () => Livewire::actingAs($user)
        ->test('pages::bookmarks')
        ->call('removeBookmark', $bookmark->id)
    )->toThrow(ModelNotFoundException::class);

    expect(Bookmark::find($bookmark->id))->not->toBeNull();
});
