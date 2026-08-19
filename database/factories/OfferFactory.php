<?php

namespace Database\Factories;

use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /** * Define the model's default state. * * @return array<string, mixed> */
    public function definition(): array
    {
        return ['request_id' => MarketplaceRequest::factory(),
            'user_id' => User::factory(),
            'price' => fake()->randomFloat(2, 100, 1000),
            'condition' => fake()->randomElement(['new', 'like_new', 'good', 'fair']),
            'message' => fake()->sentence(),
            'status' => 'pending',
            'expires_at' => now()->addWeek()];
    }

    /** * Pending offer. */
    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    /** * Accepted offer. */
    public function accepted(): static
    {
        return $this->state(['status' => 'accepted']);
    }

    /** * Rejected offer. */
    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    /** * Withdrawn offer. */
    public function withdrawn(): static
    {
        return $this->state(['status' => 'withdrawn']);
    }

    /** * Expired offer. */
    public function expired(): static
    {
        return $this->state(['status' => 'expired', 'expires_at' => now()->subDay()]);
    }
}
