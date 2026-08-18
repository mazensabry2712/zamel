<?php

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an active user to favorite a public listing', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson(
        "/api/v1/listings/{$listing->id}/favorite"
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.listing_id', $listing->id)
        ->assertJsonPath('data.listing.id', $listing->id);

    expect($user->favorites()->where('listing_id', $listing->id)->exists())
        ->toBeTrue();
});

it('requires authentication to favorite a listing', function () {
    $listing = Listing::factory()->published()->create();

    $this->postJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )->assertUnauthorized();
});

it('does not allow favoriting a non public listing', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )->assertNotFound();
});

it('does not allow favoriting the same listing twice', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create();

    Sanctum::actingAs($user);

    $this->postJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )->assertCreated();

    $this->postJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )
        ->assertStatus(409)
        ->assertJsonPath('success', false);

    expect($user->favorites()->where('listing_id', $listing->id)->count())
        ->toBe(1);
});

it('allows a user to remove their favorite', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create();

    Favorite::create([
        'user_id' => $user->id,
        'listing_id' => $listing->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($user->favorites()->where('listing_id', $listing->id)->exists())
        ->toBeFalse();
});

it('prevents a user from deleting another users favorite', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create();

    Favorite::create([
        'user_id' => $owner->id,
        'listing_id' => $listing->id,
    ]);

    Sanctum::actingAs($otherUser);

    $this->deleteJson(
        "/api/v1/listings/{$listing->id}/favorite"
    )->assertNotFound();
});

it('lists only the authenticated users public favorites', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $visibleListing = Listing::factory()->published()->create([
        'title' => 'Visible Favorite',
    ]);

    $draftListing = Listing::factory()->create([
        'title' => 'Draft Favorite',
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    $otherListing = Listing::factory()->published()->create([
        'title' => 'Other User Favorite',
    ]);

    Favorite::create([
        'user_id' => $user->id,
        'listing_id' => $visibleListing->id,
    ]);

    Favorite::create([
        'user_id' => $user->id,
        'listing_id' => $draftListing->id,
    ]);

    Favorite::create([
        'user_id' => $otherUser->id,
        'listing_id' => $otherListing->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/favorites');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.listing_id', $visibleListing->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('paginates favorites', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listings = Listing::factory()
        ->published()
        ->count(16)
        ->create();

    foreach ($listings as $listing) {
        Favorite::create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/favorites');

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('data'))->toHaveCount(15);
});
