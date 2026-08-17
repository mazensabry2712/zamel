<?php

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only publicly visible approved listings', function () {
    $visible = Listing::factory()->published()->create([
        'title' => 'Visible Listing',
    ]);

    Listing::factory()->create([
        'title' => 'Draft Listing',
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    Listing::factory()->create([
        'title' => 'Pending Listing',
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    Listing::factory()->create([
        'title' => 'Rejected Listing',
        'status' => 'draft',
        'moderation_status' => 'rejected',
    ]);

    Listing::factory()->create([
        'title' => 'Paused Listing',
        'status' => 'paused',
        'moderation_status' => 'approved',
    ]);

    Listing::factory()->create([
        'title' => 'Sold Listing',
        'status' => 'sold',
        'moderation_status' => 'approved',
    ]);

    $response = $this->getJson('/api/v1/listings')
        ->assertOk()
        ->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonPath('data.0.title', 'Visible Listing')
        ->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);

    expect($response->json('data'))->toHaveCount(1);
});

it('hides listings whose category is no longer publicly available', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => false,
    ]);

    Listing::factory()->published()->create([
        'category_id' => $category->id,
    ]);

    $response = $this->getJson('/api/v1/listings')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('paginates the public listing feed', function () {
    Listing::factory()
        ->published()
        ->count(16)
        ->create();

    $response = $this->getJson('/api/v1/listings')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('data'))->toHaveCount(15);
});
