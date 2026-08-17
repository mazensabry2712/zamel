<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows the owner to update their listing', function () {
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

    $response = $this->putJson("/api/v1/listings/{$listing->id}", [
        'title' => 'Updated Database Book',
        'price' => 300,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Database Book')
        ->assertJsonPath('data.price', '300.00')
        ->assertJsonPath('data.moderation.status', 'pending');

    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
        'title' => 'Updated Database Book',
        'price' => 300,
        'moderation_status' => 'pending',
    ]);
});

it('prevents another user from updating the listing', function () {
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
    ]);

    Sanctum::actingAs($otherUser);

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'title' => 'Unauthorized Update',
    ])->assertForbidden();
});

it('prevents assigning an unapproved category', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    $pendingCategory = Category::factory()->pending()->create();

    Sanctum::actingAs($owner);

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'category_id' => $pendingCategory->id,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);
});

it('allows partial updates', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'title' => 'Original Title',
        'price' => 200,
    ]);

    Sanctum::actingAs($owner);

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'price' => 250,
    ])->assertOk();

    $listing->refresh();

    expect($listing->price)->toBe('250.00');
    expect($listing->title)->toBe('Original Title');
});

it('requires a fresh moderation review after editing a published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'title' => 'Edited Published Listing',
    ])->assertOk();

    $listing->refresh();

    expect($listing->status)->toBe('draft');
    expect($listing->moderation_status)->toBe('pending');
    expect($listing->moderation_reason)->toBeNull();
    expect($listing->moderated_at)->toBeNull();
    expect($listing->published_at)->toBeNull();
});

it('does not allow changing moderation or lifecycle fields through update', function () {
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

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'status' => 'published',
        'moderation_status' => 'approved',
    ])->assertOk();

    $listing->refresh();

    expect($listing->status)->toBe('draft');
    expect($listing->moderation_status)->toBe('pending');
});

it('requires authentication to update a listing', function () {
    $listing = Listing::factory()->create();

    $this->putJson("/api/v1/listings/{$listing->id}", [
        'title' => 'Updated Title',
    ])->assertUnauthorized();
});
