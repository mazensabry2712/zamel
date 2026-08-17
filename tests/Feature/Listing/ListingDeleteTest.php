<?php

use App\Models\Listing;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows the owner to delete their listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->deleteJson("/api/v1/listings/{$listing->id}");

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Listing deleted successfully.',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('listings', [
        'id' => $listing->id,
    ]);
});

it('prevents another user from deleting the listing', function () {
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

    $this->deleteJson("/api/v1/listings/{$listing->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
    ]);
});

it('requires authentication to delete a listing', function () {
    $listing = Listing::factory()->create();

    $this->deleteJson("/api/v1/listings/{$listing->id}")
        ->assertUnauthorized();

    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
    ]);
});

it('returns not found when deleting a non existing listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson('/api/v1/listings/999999')
        ->assertNotFound();
});
