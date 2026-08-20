<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $reservation = Reservation::factory()->create();

        $listing = $reservation->listing;
        $buyer = $reservation->user;
        $seller = $listing->user;

        $amount = (float) $listing->price;

        $buyerFee = round($amount * 0.10, 2);
        $sellerFee = round($amount * 0.10, 2);

        return [
            'reservation_id' => $reservation->id,
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => $amount,
            'platform_buyer_fee' => $buyerFee,
            'platform_seller_fee' => $sellerFee,
            'total_amount' => $amount + $buyerFee,
            'seller_amount' => $amount - $sellerFee,
            'status' => 'pending',
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
        ]);
    }

    public function inDelivery(): static
    {
        return $this->state(fn () => [
            'status' => 'in_delivery',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => [
            'status' => 'delivered',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => 'refunded',
        ]);
    }
}
