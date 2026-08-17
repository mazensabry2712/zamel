<?php

use App\Models\Listing;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows an admin to approve a pending listing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'pending',
        'moderation_reason' => null,
        'moderated_at' => null,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/approve"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.moderation.status', 'approved');

    $listing->refresh();

    expect($listing->moderation_status)->toBe('approved');
    expect($listing->moderation_reason)->toBeNull();
    expect($listing->moderated_at)->not->toBeNull();
});

it('allows an admin to reject a pending listing with a reason', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($admin);

    $reason = 'The listing violates marketplace rules.';

    $response = $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/reject",
        [
            'reason' => $reason,
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.moderation.status', 'rejected');

    $listing->refresh();

    expect($listing->moderation_status)->toBe('rejected');
    expect($listing->moderation_reason)->toBe($reason);
    expect($listing->moderated_at)->not->toBeNull();
});

it('requires a reason when rejecting a listing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($admin);

    $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/reject"
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

it('prevents students from approving listings', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($student);

    $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/approve"
    )->assertForbidden();
});

it('prevents unauthenticated users from moderating listings', function () {
    $listing = Listing::factory()->create([
        'moderation_status' => 'pending',
    ]);

    $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/approve"
    )->assertUnauthorized();
});

it('cannot approve an already approved listing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'approved',
    ]);

    Sanctum::actingAs($admin);

    $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/approve"
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('cannot reject an already rejected listing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'moderation_status' => 'rejected',
        'moderation_reason' => 'Old reason',
    ]);

    Sanctum::actingAs($admin);

    $this->putJson(
        "/api/v1/admin/listings/{$listing->id}/reject",
        [
            'reason' => 'Another reason',
        ]
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
