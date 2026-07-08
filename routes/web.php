<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/images/{image}', 'pages::images.show')->name('images.show');
Route::livewire('/stories/{story}', 'pages::stories.show')->name('stories.show');

Route::middleware('auth')->group(function () {
    Route::livewire('/images/{image}/stories/create', 'pages::stories.create')->name('stories.create');
    Route::livewire('/bookmarks', 'pages::bookmarks')->name('bookmarks');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::livewire('/admin/images/create', 'pages::admin.create-image')->name('admin.images.create');
});

require __DIR__.'/settings.php';
