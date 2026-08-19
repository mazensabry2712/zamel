<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createReservationScenario(): array
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

    return compact('seller', 'buyer', 'category', 'listing');
}

it('allows an active user to create a reservation for a published listing', function () {
    $scenario = createReservationScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    );

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.listing_id', $scenario['listing']->id)
        ->assertJsonPath('data.user_id', $scenario['buyer']->id)
        ->assertJsonPath('data.offer_id', null)
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('reservations', [
        'listing_id' => $scenario['listing']->id,
        'user_id' => $scenario['buyer']->id,
        'offer_id' => null,
        'status' => 'pending',
    ]);
});

it('requires authentication to create a reservation', function () {
    $scenario = createReservationScenario();

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnauthorized();
});

it('prevents the listing owner from creating a reservation', function () {
    $scenario = createReservationScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnprocessable();
});

it('prevents reserving an unpublished listing', function () {
    $scenario = createReservationScenario();

    $scenario['listing']->update([
        'status' => 'draft',
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnprocessable();
});

it('prevents reserving an unapproved listing', function () {
    $scenario = createReservationScenario();

    $scenario['listing']->update([
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnprocessable();
});

it('prevents a second reservation when an active pending reservation exists', function () {
    $scenario = createReservationScenario();

    Reservation::factory()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
        'status' => 'pending',
        'expires_at' => now()->addDay(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnprocessable();
});

it('prevents a second reservation when an active confirmed reservation exists', function () {
    $scenario = createReservationScenario();

    Reservation::factory()->confirmed()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
        'expires_at' => now()->addDay(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )->assertUnprocessable();
});

it('allows a new reservation when previous reservations are cancelled', function () {
    $scenario = createReservationScenario();

    Reservation::factory()->cancelled()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});

it('allows a new reservation when previous reservations are completed', function () {
    $scenario = createReservationScenario();

    Reservation::factory()->completed()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});

it('allows a new reservation when a previous pending reservation has expired', function () {
    $scenario = createReservationScenario();

    Reservation::factory()->expired()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/listings/{$scenario['listing']->id}/reservation"
    )
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});
