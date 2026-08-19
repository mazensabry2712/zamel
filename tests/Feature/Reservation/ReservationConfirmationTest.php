<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createConfirmationScenario(): array
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

it('allows the listing owner to confirm a pending reservation', function () {
    $scenario = createConfirmationScenario();

    Sanctum::actingAs($scenario['seller']);

    $response = $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.listing_id', $scenario['listing']->id)
        ->assertJsonPath('data.user_id', $scenario['buyer']->id);

    $this->assertDatabaseHas('reservations', [
        'id' => $scenario['reservation']->id,
        'status' => 'confirmed',
    ]);

    expect(Reservation::find($scenario['reservation']->id)->confirmed_at)->not->toBeNull();
});

it('requires authentication to confirm a reservation', function () {
    $scenario = createConfirmationScenario();

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertUnauthorized();
});

it('prevents the buyer from confirming their own reservation', function () {
    $scenario = createConfirmationScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertForbidden();
});

it('prevents another user from confirming a reservation', function () {
    $scenario = createConfirmationScenario();
    $otherUser = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertForbidden();
});

it('prevents confirming a non-pending reservation', function () {
    $scenario = createConfirmationScenario();
    $scenario['reservation']->update(['status' => 'cancelled']);

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertUnprocessable();
});

it('prevents confirming an expired reservation', function () {
    $scenario = createConfirmationScenario();
    $scenario['reservation']->update([
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertUnprocessable();
});

it('prevents confirming a reservation that does not belong to the listing', function () {
    $scenario = createConfirmationScenario();
    $otherListing = Listing::factory()->published()->create([
        'user_id' => $scenario['seller']->id,
        'category_id' => $scenario['category']->id,
    ]);

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$otherListing->id}/reservation/{$scenario['reservation']->id}/confirm"
    )->assertNotFound();
});
