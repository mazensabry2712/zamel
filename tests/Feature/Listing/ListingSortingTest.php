<?php

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sorts listings by newest by default', function () {
    $category = Category::factory()->create();

    $older = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Older Listing',
        'created_at' => now()->subDay(),
    ]);

    $newer = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Newer Listing',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/listings');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $newer->id)
        ->assertJsonPath('data.1.id', $older->id);
});

it('sorts listings by oldest', function () {
    $category = Category::factory()->create();

    $older = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'created_at' => now()->subDay(),
    ]);

    $newer = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/listings?sort=oldest');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $older->id)
        ->assertJsonPath('data.1.id', $newer->id);
});

it('sorts listings by lowest price first', function () {
    $category = Category::factory()->create();

    $expensive = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 500,
    ]);

    $cheap = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 100,
    ]);

    $response = $this->getJson('/api/v1/listings?sort=price_asc');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $cheap->id)
        ->assertJsonPath('data.1.id', $expensive->id);
});

it('sorts listings by highest price first', function () {
    $category = Category::factory()->create();

    $cheap = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 100,
    ]);

    $expensive = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 500,
    ]);

    $response = $this->getJson('/api/v1/listings?sort=price_desc');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $expensive->id)
        ->assertJsonPath('data.1.id', $cheap->id);
});

it('keeps sorting and filters in pagination links', function () {
    $category = Category::factory()->create();

    Listing::factory()
        ->published()
        ->count(16)
        ->create([
            'category_id' => $category->id,
            'condition' => 'good',
            'price' => 250,
        ]);

    $response = $this->getJson(
        '/api/v1/listings?condition=good&price_min=100&price_max=500&sort=price_desc'
    );

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('links.next'))
        ->toContain('condition=good')
        ->toContain('price_min=100')
        ->toContain('price_max=500')
        ->toContain('sort=price_desc');
});
