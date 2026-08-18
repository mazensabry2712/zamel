<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @extends Factory<MarketplaceRequest>
 */
class MarketplaceRequestFactory extends Factory
{
    use HasFactory;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'budget' => fake()->randomFloat(2, 100, 1000),
            'status' => 'open',
            'expires_at' => now()->addWeek(),
        ];
    }
}
