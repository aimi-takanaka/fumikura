<?php

use App\ImageCreditType;
use App\Models\Image;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to the login page', function () {
    $image = Image::factory()->create();

    $this->get(route('admin.images.edit', $image))
        ->assertRedirect(route('login'));
});

it('forbids non-admin users', function () {
    $image = Image::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.images.edit', $image))
        ->assertForbidden();
});

it('lets admins view the edit form with existing values', function () {
    $image = Image::factory()->photoCredit('Sato Hanako', 'Unsplash')->create([
        'title' => '港の朝',
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.images.edit', $image))
        ->assertOk()
        ->assertSee('お題画像の編集');

    Livewire::actingAs($admin)
        ->test('pages::admin.edit-image', ['image' => $image])
        ->assertSet('title', '港の朝')
        ->assertSet('creditType', 'photo')
        ->assertSet('creditAuthor', 'Sato Hanako')
        ->assertSet('creditService', 'Unsplash')
        ->assertSet('creditServiceUrl', 'https://unsplash.com');
});

it('updates credit attribution on an existing image', function () {
    $admin = User::factory()->admin()->create();
    $image = Image::factory()->create(['title' => '古いタイトル']);

    Livewire::actingAs($admin)
        ->test('pages::admin.edit-image', ['image' => $image])
        ->set('title', '新しいタイトル')
        ->set('creditType', 'illustration')
        ->set('creditAuthor', 'Yamada Taro')
        ->set('creditService', 'Example Art')
        ->set('creditServiceUrl', 'https://example.com/art')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('images.show', $image));

    $image->refresh();

    expect($image->title)->toBe('新しいタイトル')
        ->and($image->credit_type)->toBe(ImageCreditType::Illustration)
        ->and($image->credit_author)->toBe('Yamada Taro')
        ->and($image->credit_service)->toBe('Example Art')
        ->and($image->credit_service_url)->toBe('https://example.com/art')
        ->and($image->creditLine())->toBe('イラスト：Yamada Taro　Example Art');
});

it('clears credit when type is unset', function () {
    $admin = User::factory()->admin()->create();
    $image = Image::factory()->photoCredit('Sato Hanako', 'Unsplash')->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.edit-image', ['image' => $image])
        ->set('creditType', null)
        ->set('creditAuthor', '')
        ->set('creditService', '')
        ->set('creditServiceUrl', '')
        ->call('save')
        ->assertHasNoErrors();

    $image->refresh();

    expect($image->credit_type)->toBeNull()
        ->and($image->credit_author)->toBeNull()
        ->and($image->credit_service)->toBeNull()
        ->and($image->credit_service_url)->toBeNull()
        ->and($image->creditLine())->toBeNull();
});

it('shows an edit link to admins on the image detail page', function () {
    $image = Image::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('images.show', $image))
        ->assertOk()
        ->assertSee(route('admin.images.edit', $image), false);
});

it('hides the edit link from non-admin users', function () {
    $image = Image::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('images.show', $image))
        ->assertOk()
        ->assertDontSee(route('admin.images.edit', $image), false);
});
