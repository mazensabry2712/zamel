<?php

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters listings by category', function () {
    $books = Category::factory()->create([
        'name' => 'Books',
    ]);

    $electronics = Category::factory()->create([
        'name' => 'Electronics',
    ]);

    $bookListing = Listing::factory()
        ->published()
        ->create([
            'category_id' => $books->id,
            'title' => 'Laravel Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $electronics->id,
            'title' => 'Laptop',
        ]);

    $response = $this->getJson(
        "/api/v1/listings?category_id={$books->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $bookListing->id)
        ->assertJsonPath('data.0.title', 'Laravel Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('returns an empty result when the category has no published listings', function () {
    $books = Category::factory()->create();

    $electronics = Category::factory()->create();

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $electronics->id,
        ]);

    $response = $this->getJson(
        "/api/v1/listings?category_id={$books->id}"
    );

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('combines category filter with search', function () {
    $books = Category::factory()->create();

    $electronics = Category::factory()->create();

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $books->id,
            'title' => 'Laravel Database Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $books->id,
            'title' => 'Clean Code Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'category_id' => $electronics->id,
            'title' => 'Laravel Laptop',
        ]);

    $response = $this->getJson(
        "/api/v1/listings?category_id={$books->id}&search=Laravel"
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.0.title',
            'Laravel Database Book'
        );

    expect($response->json('data'))->toHaveCount(1);
});

it('keeps category filter in pagination links', function () {
    $books = Category::factory()->create();

    Listing::factory()
        ->published()
        ->count(16)
        ->create([
            'category_id' => $books->id,
        ]);

    $response = $this->getJson(
        "/api/v1/listings?category_id={$books->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('links.next'))
        ->toContain('category_id=' . $books->id);
});
