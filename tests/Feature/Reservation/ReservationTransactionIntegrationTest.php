<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createReservationTransactionScenario(): array
{
    $seller = User::factory()->create([
        'status' => 'active',
    ]);

    $buyer = User::factory()->create([
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $listing = Listing::factory()
        ->published()
        ->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
        ]);

    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'user_id' => $buyer->id,
        'status' => 'pending',
        'reserved_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    return compact('seller', 'buyer', 'category', 'listing', 'reservation');
}

it('creates a pending transaction when the seller confirms a reservation', function () {
    $scenario = createReservationTransactionScenario();

    Sanctum::actingAs($scenario['seller']);

    $response = $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    );

    $response->assertOk();

    $reservation = Reservation::findOrFail($scenario['reservation']->id);
    $transaction = Transaction::where('reservation_id', $reservation->id)->first();

    expect($reservation->status)->toBe('confirmed');
    expect($transaction)->not->toBeNull();
    expect($transaction->listing_id)->toBe($scenario['listing']->id);
    expect($transaction->buyer_id)->toBe($scenario['buyer']->id);
    expect($transaction->seller_id)->toBe($scenario['seller']->id);
    expect($transaction->amount)->toEqual($scenario['listing']->price);
    expect($transaction->status)->toBe('pending');

    $this->assertDatabaseHas('transactions', [
        'reservation_id' => $reservation->id,
        'listing_id' => $scenario['listing']->id,
        'buyer_id' => $scenario['buyer']->id,
        'seller_id' => $scenario['seller']->id,
        'status' => 'pending',
    ]);
});
