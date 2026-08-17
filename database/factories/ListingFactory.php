<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state([
                'status' => 'active',
                'role' => 'student',
            ]),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 1000),
            'condition' => fake()->randomElement([
                'new',
                'like_new',
                'good',
                'fair',
            ]),
            'status' => 'draft',
            'moderation_status' => 'pending',
            'moderation_reason' => null,
            'moderated_at' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'moderation_status' => 'approved',
            'published_at' => now(),
            'moderated_at' => now(),
        ]);
    }
}
