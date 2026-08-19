<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceRequest>
 */
class MarketplaceRequestFactory extends Factory
{
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

    public function open(): static
    {
        return $this->state([
            'status' => 'open',
            'expires_at' => now()->addWeek(),
        ]);
    }

    public function fulfilled(): static
    {
        return $this->state([
            'status' => 'fulfilled',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function offers()
{
    return $this->hasMany(Offer::class, 'request_id');
}
}
