<?php

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches listings by title', function () {
    Listing::factory()->published()->create([
        'title' => 'Laravel Database Book',
    ]);

    Listing::factory()->published()->create([
        'title' => 'Clean Code Book',
    ]);

    $response = $this->getJson('/api/v1/listings?search=Laravel');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Laravel Database Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('searches listings by description', function () {
    Listing::factory()->published()->create([
        'title' => 'Programming Book',
        'description' => 'Complete Laravel backend development guide.',
    ]);

    Listing::factory()->published()->create([
        'title' => 'Clean Code',
        'description' => 'Software engineering fundamentals.',
    ]);

    $response = $this->getJson('/api/v1/listings?search=Laravel');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Programming Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('returns an empty result when nothing matches', function () {
    Listing::factory()->published()->create([
        'title' => 'Laravel Book',
    ]);

    $response = $this->getJson('/api/v1/listings?search=Python');

    $response
        ->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('does not expose non-public listings through search', function () {
    Listing::factory()->published()->create([
        'title' => 'Public Laravel Book',
    ]);

    Listing::factory()->create([
        'title' => 'Hidden Laravel Book',
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    $response = $this->getJson('/api/v1/listings?search=Laravel');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Public Laravel Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('keeps pagination working with search', function () {
    Listing::factory()
        ->published()
        ->count(16)
        ->create([
            'title' => 'Laravel Book',
        ]);

    $response = $this->getJson('/api/v1/listings?search=Laravel');

    $response
        ->assertOk()
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('data'))->toHaveCount(15);
});
