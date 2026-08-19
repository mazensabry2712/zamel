<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Offer;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'user_id' => User::factory(),
            'offer_id' => null,
            'status' => 'pending',
            'reserved_at' => now(),
            'confirmed_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'expires_at' => now()->addDay(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'confirmed_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);
    }
}
