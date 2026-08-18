<?php

namespace Database\Factories;

use App\ImageCreditType;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'credit_type' => null,
            'credit_author' => null,
            'credit_service' => null,
            'file_path' => 'images/'.fake()->uuid().'.jpg',
            'user_id' => User::factory()->admin(),
        ];
    }

    /**
     * Attribution for an illustration.
     */
    public function illustrationCredit(string $author = 'Artist Name', string $service = 'Example Art'): static
    {
        return $this->state(fn (): array => [
            'credit_type' => ImageCreditType::Illustration,
            'credit_author' => $author,
            'credit_service' => $service,
        ]);
    }

    /**
     * Attribution for a photo.
     */
    public function photoCredit(string $author = 'Photographer Name', string $service = 'Unsplash'): static
    {
        return $this->state(fn (): array => [
            'credit_type' => ImageCreditType::Photo,
            'credit_author' => $author,
            'credit_service' => $service,
        ]);
    }
}
