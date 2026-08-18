<?php

use App\ImageCreditType;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('redirects guests to the login page', function () {
    $this->get(route('admin.images.create'))
        ->assertRedirect(route('login'));
});

it('forbids non-admin users', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.images.create'))
        ->assertForbidden();
});

it('lets admins view the upload form', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.images.create'))
        ->assertOk()
        ->assertSee('お題画像の投稿');
});

it('stores an uploaded image and creates the record', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.create-image')
        ->set('title', '夕暮れの海辺')
        ->set('photo', UploadedFile::fake()->create('sunset.jpg', 100, 'image/jpeg'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('topics.index'));

    $image = Image::first();

    expect($image)->not->toBeNull()
        ->and($image->title)->toBe('夕暮れの海辺')
        ->and($image->credit_type)->toBeNull()
        ->and($image->user_id)->toBe($admin->id);

    Storage::disk('public')->assertExists($image->file_path);
});

it('stores photo credit when provided', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.create-image')
        ->set('title', '港の写真')
        ->set('creditType', 'photo')
        ->set('creditAuthor', 'Sato Hanako')
        ->set('creditService', 'Unsplash')
        ->set('photo', UploadedFile::fake()->create('harbor.jpg', 100, 'image/jpeg'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('topics.index'));

    $image = Image::first();

    expect($image)->not->toBeNull()
        ->and($image->credit_type)->toBe(ImageCreditType::Photo)
        ->and($image->credit_author)->toBe('Sato Hanako')
        ->and($image->credit_service)->toBe('Unsplash')
        ->and($image->creditLine())->toBe('photo by Sato Hanako　Unsplash');
});

it('requires author and service when a credit type is selected', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.create-image')
        ->set('title', 'テスト')
        ->set('creditType', 'illustration')
        ->set('creditAuthor', '')
        ->set('creditService', '')
        ->set('photo', UploadedFile::fake()->create('art.jpg', 100, 'image/jpeg'))
        ->call('save')
        ->assertHasErrors(['creditAuthor', 'creditService']);
});

it('requires a title and an image file', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.create-image')
        ->set('title', '')
        ->set('photo', null)
        ->call('save')
        ->assertHasErrors(['title', 'photo']);
});

it('rejects non-image files', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.create-image')
        ->set('title', 'テスト')
        ->set('photo', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasErrors(['photo']);
});
