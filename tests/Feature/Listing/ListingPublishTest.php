<?php

use App\Models\Listing;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows the owner to publish an approved draft listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'moderation_status' => 'approved',
        'published_at' => null,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->postJson(
        "/api/v1/listings/{$listing->id}/publish"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'published');

    $listing->refresh();

    expect($listing->status)->toBe('published');
    expect($listing->published_at)->not->toBeNull();
});

it('prevents publishing a pending listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson(
        "/api/v1/listings/{$listing->id}/publish"
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing']);
});

it('prevents another user from publishing the listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'draft',
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/listings/{$listing->id}/publish"
    )->assertForbidden();
});

it('prevents publishing an already published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $this->postJson(
        "/api/v1/listings/{$listing->id}/publish"
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing']);
});