<?php

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
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
        ->assertSee('taro@example.com')
        ->assertSee('自分の投稿')
        ->assertSee('お気に入り一覧')
        ->assertSee(route('bookmarks'), false);
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

it('shows the topic thumbnail below the topic title on my stories', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $image = Image::factory()->create([
        'title' => '海辺のお題',
        'file_path' => 'images/beach.jpg',
    ]);

    Storage::disk('public')->put('images/beach.jpg', 'image-content');

    Story::factory()->for($user)->for($image)->complete()->create(['title' => '夏の物語']);

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk()
        ->assertSee('海辺のお題')
        ->assertSee($image->url(), false);
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

it('filters stories by complete status', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($user)->draft()->create(['title' => '下書きの物語']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('statusFilter', 'complete')
        ->assertSee('完成した物語')
        ->assertDontSee('下書きの物語');
});

it('filters stories by draft status', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($user)->idea()->create(['title' => 'アイデアの物語']);
    Story::factory()->for($user)->draft()->create(['title' => '下書きの物語']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('statusFilter', 'draft')
        ->assertSee('下書きの物語')
        ->assertDontSee('完成した物語')
        ->assertDontSee('アイデアの物語');
});

it('filters stories by idea status', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '完成した物語']);
    Story::factory()->for($user)->idea()->create(['title' => 'アイデアの物語']);
    Story::factory()->for($user)->draft()->create(['title' => '下書きの物語']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('statusFilter', 'idea')
        ->assertSee('アイデアの物語')
        ->assertDontSee('完成した物語')
        ->assertDontSee('下書きの物語');
});

it('searches stories by title', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '海辺の夕暮れ']);
    Story::factory()->for($user)->complete()->create(['title' => '森の朝']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('search', '海辺')
        ->assertSee('海辺の夕暮れ')
        ->assertDontSee('森の朝');
});

it('searches stories by body', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create([
        'title' => '無関係なタイトル',
        'body' => '波の音が静かに響いていた。',
    ]);
    Story::factory()->for($user)->complete()->create([
        'title' => '別の物語',
        'body' => '森の中を歩いた。',
    ]);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('search', '波の音')
        ->assertSee('無関係なタイトル')
        ->assertDontSee('別の物語');
});

it('searches stories by image title', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create([
        'title' => '無関係なタイトル',
        'body' => '無関係な本文',
        'image_id' => Image::factory()->create(['title' => '夕焼けの海'])->id,
    ]);
    Story::factory()->for($user)->complete()->create([
        'title' => '別の物語',
        'body' => '別の本文',
        'image_id' => Image::factory()->create(['title' => '朝の森'])->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('search', '夕焼け')
        ->assertSee('無関係なタイトル')
        ->assertDontSee('別の物語');
});

it('shows a filtered empty state when no stories match', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '完成した物語']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('search', '存在しないキーワード')
        ->assertSee('条件に合う投稿がありません')
        ->assertDontSee('完成した物語');
});

it('combines status filter and title search', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->complete()->create(['title' => '海辺の完成作']);
    Story::factory()->for($user)->draft()->create(['title' => '海辺の下書き']);
    Story::factory()->for($user)->complete()->create(['title' => '森の完成作']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->set('statusFilter', 'complete')
        ->set('search', '海辺')
        ->assertSee('海辺の完成作')
        ->assertDontSee('海辺の下書き')
        ->assertDontSee('森の完成作');
});

it('paginates stories when there are more than ten', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->count(11)->create();

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->assertSee('Showing 1 to 10 of 11 results', false)
        ->call('nextPage')
        ->assertSee('Showing 11 to 11 of 11 results', false);
});

it('does not show pagination when there are ten or fewer stories', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->count(10)->create();

    $this->actingAs($user)
        ->get(route('mypage'))
        ->assertOk()
        ->assertDontSee('data-flux-pagination', false);
});

it('resets to the first page when search changes', function () {
    $user = User::factory()->create();

    Story::factory()->for($user)->count(10)->create(['title' => '普通の物語']);
    Story::factory()->for($user)->create(['title' => '特別な物語']);

    Livewire::actingAs($user)
        ->test('pages::mypage')
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->assertSee('特別な物語')
        ->set('search', '特別')
        ->assertSet('paginators.page', 1)
        ->assertSee('特別な物語')
        ->assertDontSee('条件に合う投稿がありません');
});
