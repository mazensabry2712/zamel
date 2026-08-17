<?php

use App\Models\Listing;
use App\Models\User;

it('allows the owner to update their listing', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($user->can('update', $listing))->toBeTrue();
});

it('prevents another user from updating the listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    expect($otherUser->can('update', $listing))->toBeFalse();
});

it('allows the owner to delete their listing', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($user->can('delete', $listing))->toBeTrue();
});
