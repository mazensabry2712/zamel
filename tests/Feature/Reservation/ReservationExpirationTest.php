<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createExpirationScenario(): array
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

    return compact('seller', 'buyer', 'category', 'listing');
}

it('expires pending reservations whose expiry time has passed', function () {
    $scenario = createExpirationScenario();

    $reservation = Reservation::factory()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => $scenario['buyer']->id,
        'status' => 'pending',
        'expires_at' => now()->subMinute(),
    ]);

    $this->artisan('reservations:expire')
        ->assertSuccessful()
        ->expectsOutput('Expired 1 reservation(s).');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'expired',
    ]);
});

it('does not expire pending reservations that have not reached their expiry time', function () {
    $scenario = createExpirationScenario();

    $reservation = Reservation::factory()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => $scenario['buyer']->id,
        'status' => 'pending',
        'expires_at' => now()->addMinute(),
    ]);

    $this->artisan('reservations:expire')
        ->assertSuccessful()
        ->expectsOutput('Expired 0 reservation(s).');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'pending',
    ]);
});

it('does not expire confirmed reservations', function () {
    $scenario = createExpirationScenario();

    $reservation = Reservation::factory()->confirmed()->create([
        'listing_id' => $scenario['listing']->id,
        'user_id' => $scenario['buyer']->id,
        'expires_at' => now()->subMinute(),
    ]);

    $this->artisan('reservations:expire')
        ->assertSuccessful()
        ->expectsOutput('Expired 0 reservation(s).');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'confirmed',
    ]);
});
