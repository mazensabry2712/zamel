<?php

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters listings by condition', function () {
    $category = Category::factory()->create();

    $good = Listing::factory()
        ->published()
        ->create([
            'category_id' => $category->id,
            'condition' => 'good',
            'title' => 'Good Condition Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $category->id,
            'condition' => 'new',
            'title' => 'New Condition Book',
        ]);

    $response = $this->getJson('/api/v1/listings?condition=good');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $good->id)
        ->assertJsonPath('data.0.condition', 'good');

    expect($response->json('data'))->toHaveCount(1);
});

it('returns no listings when no listing matches the condition', function () {
    $category = Category::factory()->create();

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $category->id,
            'condition' => 'new',
        ]);

    $response = $this->getJson('/api/v1/listings?condition=fair');

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('combines condition filter with search', function () {
    $category = Category::factory()->create();

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $category->id,
            'condition' => 'good',
            'title' => 'Laravel Good Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $category->id,
            'condition' => 'fair',
            'title' => 'Laravel Fair Book',
        ]);

    $response = $this->getJson('/api/v1/listings?condition=good&search=Laravel');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Laravel Good Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('preserves condition filter during pagination', function () {
    $category = Category::factory()->create();

    Listing::factory()
        ->published()
        ->count(16)
        ->create([
            'category_id' => $category->id,
            'condition' => 'good',
        ]);

    $response = $this->getJson('/api/v1/listings?condition=good');

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('links.next'))
        ->toContain('condition=good');
});
