<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Profile;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters listings by minimum price', function () {
    $category = Category::factory()->create();

    Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 100,
        'title' => 'Cheap Book',
    ]);

    $matching = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 300,
        'title' => 'Mid Price Book',
    ]);

    $response = $this->getJson('/api/v1/listings?price_min=250');

    $response->assertOk()->assertJsonPath('data.0.id', $matching->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('filters listings by maximum price', function () {
    $category = Category::factory()->create();

    $matching = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 100,
        'title' => 'Cheap Book',
    ]);

    Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 600,
        'title' => 'Expensive Book',
    ]);

    $response = $this->getJson('/api/v1/listings?price_max=250');

    $response->assertOk()->assertJsonPath('data.0.id', $matching->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('filters listings within a price range', function () {
    $category = Category::factory()->create();

    Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 50,
    ]);

    $matching = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 300,
        'title' => 'Matching Book',
    ]);

    Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 900,
    ]);

    $response = $this->getJson('/api/v1/listings?price_min=200&price_max=500');

    $response->assertOk()->assertJsonPath('data.0.id', $matching->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('combines price filter with search', function () {
    $category = Category::factory()->create();

    $matching = Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 300,
        'title' => 'Laravel Database Book',
    ]);

    Listing::factory()->published()->create([
        'category_id' => $category->id,
        'price' => 900,
        'title' => 'Laravel Advanced Book',
    ]);

    $response = $this->getJson('/api/v1/listings?search=Laravel&price_max=500');

    $response->assertOk()->assertJsonPath('data.0.id', $matching->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('combines price category and university filters', function () {
    $university = University::create([
        'name' => 'Price Filter University',
        'slug' => 'price-filter-university',
    ]);

    $category = Category::factory()->create();

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::create([
        'user_id' => $user->id,
        'education_type' => 'university',
        'university_id' => $university->id,
    ]);

    $matching = Listing::factory()->published()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'price' => 300,
        'title' => 'Matching Book',
    ]);

    Listing::factory()->published()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'price' => 900,
        'title' => 'Expensive Book',
    ]);

    $response = $this->getJson(
        "/api/v1/listings?category_id={$category->id}&university_id={$university->id}&price_max=500"
    );

    $response->assertOk()->assertJsonPath('data.0.id', $matching->id);

    expect($response->json('data'))->toHaveCount(1);
});

it('preserves price filters during pagination', function () {
    $category = Category::factory()->create();

    Listing::factory()->published()->count(16)->create([
        'category_id' => $category->id,
        'price' => 300,
    ]);

    $response = $this->getJson('/api/v1/listings?price_min=200&price_max=500');

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('links.next'))
        ->toContain('price_min=200')
        ->and($response->json('links.next'))
        ->toContain('price_max=500');
});
