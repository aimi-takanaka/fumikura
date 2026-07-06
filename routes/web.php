<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/images/{image}', 'pages::images.show')->name('images.show');

Route::middleware('auth')->group(function () {
    Route::livewire('/images/{image}/stories/create', 'pages::stories.create')->name('stories.create');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::livewire('/admin/images/create', 'pages::admin.create-image')->name('admin.images.create');
});

require __DIR__.'/settings.php';
