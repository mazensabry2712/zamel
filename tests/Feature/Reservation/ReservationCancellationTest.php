<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createCancellationScenario(): array
{
    $seller = User::factory()->create(['status' => 'active']);
    $buyer = User::factory()->create(['status' => 'active']);

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

it('allows the reservation owner to cancel a pending reservation', function () {
    $scenario = createCancellationScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('data.listing_id', $scenario['listing']->id)
        ->assertJsonPath('data.user_id', $scenario['buyer']->id);

    $this->assertDatabaseHas('reservations', [
        'id' => $scenario['reservation']->id,
        'status' => 'cancelled',
    ]);

    expect(Reservation::find($scenario['reservation']->id)->cancelled_at)->not->toBeNull();
});

it('allows the listing owner to cancel a pending reservation', function () {
    $scenario = createCancellationScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

it('requires authentication to cancel a reservation', function () {
    $scenario = createCancellationScenario();

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertUnauthorized();
});

it('prevents another user from cancelling a reservation', function () {
    $scenario = createCancellationScenario();
    $otherUser = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertForbidden();
});

it('allows cancelling a confirmed reservation', function () {
    $scenario = createCancellationScenario();
    $scenario['reservation']->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

it('prevents cancelling a completed reservation', function () {
    $scenario = createCancellationScenario();
    $scenario['reservation']->update([
        'status' => 'completed',
        'confirmed_at' => now(),
        'completed_at' => now(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertUnprocessable();
});

it('prevents cancelling an already cancelled reservation', function () {
    $scenario = createCancellationScenario();
    $scenario['reservation']->update([
        'status' => 'cancelled',
        'cancelled_at' => now(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertUnprocessable();
});

it('prevents cancelling an expired pending reservation', function () {
    $scenario = createCancellationScenario();
    $scenario['reservation']->update([
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

it('prevents cancelling a reservation that does not belong to the listing', function () {
    $scenario = createCancellationScenario();
    $otherListing = Listing::factory()->published()->create([
        'user_id' => $scenario['seller']->id,
        'category_id' => $scenario['category']->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$otherListing->id}/reservation/{$scenario['reservation']->id}/cancel"
    )->assertNotFound();
});
