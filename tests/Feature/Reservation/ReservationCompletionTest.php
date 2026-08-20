<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createCompletionScenario(): array
{
    $seller = User::factory()->create(['status' => 'active']);
    $buyer = User::factory()->create(['status' => 'active']);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
    ]);

    $reservation = Reservation::factory()->confirmed()->create([
        'listing_id' => $listing->id,
        'user_id' => $buyer->id,
        'expires_at' => now()->addDay(),
    ]);

    // A confirmed reservation is expected to have a transaction in the current domain flow.
    // Reservation confirmation creates it; this fixture creates the same persisted state directly.
    Transaction::factory()->create([
        'reservation_id' => $reservation->id,
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
    ]);

    return compact('seller', 'buyer', 'category', 'listing', 'reservation');
}

it('allows the reservation owner to complete a confirmed reservation', function () {
    $scenario = createCompletionScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.listing_id', $scenario['listing']->id)
        ->assertJsonPath('data.user_id', $scenario['buyer']->id);

    $this->assertDatabaseHas('reservations', [
        'id' => $scenario['reservation']->id,
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('transactions', [
        'reservation_id' => $scenario['reservation']->id,
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('listings', [
        'id' => $scenario['listing']->id,
        'status' => 'sold',
    ]);

    expect(Reservation::find($scenario['reservation']->id)->completed_at)->not->toBeNull();
    expect(Transaction::where('reservation_id', $scenario['reservation']->id)->first()->completed_at)->not->toBeNull();
});

it('allows the listing owner to complete a confirmed reservation', function () {
    $scenario = createCompletionScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    $this->assertDatabaseHas('reservations', [
        'id' => $scenario['reservation']->id,
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('transactions', [
        'reservation_id' => $scenario['reservation']->id,
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('listings', [
        'id' => $scenario['listing']->id,
        'status' => 'sold',
    ]);
});

it('requires authentication to complete a reservation', function () {
    $scenario = createCompletionScenario();

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertUnauthorized();
});

it('prevents another user from completing a reservation', function () {
    $scenario = createCompletionScenario();
    $otherUser = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertForbidden();
});

it('prevents completing a pending reservation', function () {
    $scenario = createCompletionScenario();
    $scenario['reservation']->update(['status' => 'pending']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertUnprocessable();
});

it('prevents completing a cancelled reservation', function () {
    $scenario = createCompletionScenario();
    $scenario['reservation']->update(['status' => 'cancelled']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertUnprocessable();
});

it('prevents completing an already completed reservation', function () {
    $scenario = createCompletionScenario();
    $scenario['reservation']->update(['status' => 'completed']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertUnprocessable();
});

it('prevents completing a reservation that does not belong to the listing', function () {
    $scenario = createCompletionScenario();
    $otherListing = Listing::factory()->published()->create([
        'user_id' => $scenario['seller']->id,
        'category_id' => $scenario['category']->id,
    ]);

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$otherListing->id}/reservation/{$scenario['reservation']->id}/complete"
    )->assertNotFound();
});
