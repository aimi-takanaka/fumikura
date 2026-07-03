<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Like;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $users = User::factory(5)->create();

        $users->push(User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]));

        $images = Image::factory(5)
            ->for($admin)
            ->create();

        $images->each(function (Image $image) use ($users): void {
            $stories = Story::factory(fake()->numberBetween(2, 4))
                ->for($image)
                ->recycle($users)
                ->create();

            $stories->each(function (Story $story) use ($users): void {
                Comment::factory(fake()->numberBetween(0, 3))
                    ->for($story)
                    ->recycle($users)
                    ->create();

                $users->random(fake()->numberBetween(0, $users->count()))
                    ->each(fn (User $user) => Like::factory()->for($story)->for($user)->create());

                $users->random(fake()->numberBetween(0, $users->count()))
                    ->each(fn (User $user) => Bookmark::factory()->for($story)->for($user)->create());
            });
        });
    }
}
