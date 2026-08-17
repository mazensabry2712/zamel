<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;

it('allows public users to view a published and approved listing', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'status' => 'published',
        'moderation_status' => 'approved',
    ]);

    $response = $this->getJson(
        "/api/v1/listings/{$listing->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $listing->id)
        ->assertJsonPath('data.status', 'published')
        ->assertJsonPath('data.moderation.status', 'approved');
});
it('does not expose a pending listing publicly', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    $response = $this->getJson(
        "/api/v1/listings/{$listing->id}"
    );

    $response->assertNotFound();
});
