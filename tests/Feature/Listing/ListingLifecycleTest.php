<?php

use App\Models\Listing;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows the owner to pause a published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/v1/listings/{$listing->id}/pause")
        ->assertOk()
        ->assertJsonPath('data.status', 'paused');

    expect($listing->refresh()->status)->toBe('paused');
});

it('prevents another user from pausing a listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($otherUser);

    $this->postJson("/api/v1/listings/{$listing->id}/pause")
        ->assertForbidden();
});

it('prevents pausing a non published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/v1/listings/{$listing->id}/pause")
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing']);
});

it('allows the owner to mark a published listing as sold', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/v1/listings/{$listing->id}/sold")
        ->assertOk()
        ->assertJsonPath('data.status', 'sold');

    expect($listing->refresh()->status)->toBe('sold');
});

it('prevents another user from marking a listing as sold', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($otherUser);

    $this->postJson("/api/v1/listings/{$listing->id}/sold")
        ->assertForbidden();
});

it('prevents marking a non published listing as sold', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'paused',
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/v1/listings/{$listing->id}/sold")
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing']);
});
