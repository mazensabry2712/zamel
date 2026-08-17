<?php

use App\Models\Category;
use App\Models\Listing;

it('allows public users to view a published and approved listing', function () {
    $listing = Listing::factory()->published()->create();

    $response = $this->getJson("/api/v1/listings/{$listing->id}");

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $listing->id)
        ->assertJsonPath('data.status', 'published')
        ->assertJsonPath('data.moderation.status', 'approved');
});

it('does not expose a draft listing publicly', function () {
    $listing = Listing::factory()->create([
        'status' => 'draft',
        'moderation_status' => 'approved',
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});

it('does not expose a pending listing publicly', function () {
    $listing = Listing::factory()->create([
        'status' => 'published',
        'moderation_status' => 'pending',
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});

it('does not expose a rejected listing publicly', function () {
    $listing = Listing::factory()->create([
        'status' => 'published',
        'moderation_status' => 'rejected',
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});

it('does not expose a paused listing publicly', function () {
    $listing = Listing::factory()->create([
        'status' => 'paused',
        'moderation_status' => 'approved',
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});

it('does not expose a sold listing publicly', function () {
    $listing = Listing::factory()->create([
        'status' => 'sold',
        'moderation_status' => 'approved',
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});

it('does not expose a listing whose category is no longer approved', function () {
    $category = Category::factory()->create([
        'status' => 'rejected',
        'is_active' => false,
    ]);

    $listing = Listing::factory()->published()->create([
        'category_id' => $category->id,
    ]);

    $this->getJson("/api/v1/listings/{$listing->id}")
        ->assertNotFound();
});
