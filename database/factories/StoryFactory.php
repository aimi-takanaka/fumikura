<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use App\StoryStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Story>
 */
class StoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_id' => Image::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(StoryStatus::cases()),
        ];
    }

    /**
     * Indicate that the story is completed.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoryStatus::Complete,
        ]);
    }

    /**
     * Indicate that the story is a draft (prose / idea).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoryStatus::Draft,
        ]);
    }
}
