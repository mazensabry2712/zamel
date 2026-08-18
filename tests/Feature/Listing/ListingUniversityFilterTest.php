<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Profile;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters listings by university', function () {
    $universityOne = University::factory()->create();
    $universityTwo = University::factory()->create();

    $category = Category::factory()->create();

    $userOne = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($userOne)->create([
        'university_id' => $universityOne->id,
    ]);

    $userTwo = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($userTwo)->create([
        'university_id' => $universityTwo->id,
    ]);

    $listing = Listing::factory()
        ->published()
        ->create([
            'user_id' => $userOne->id,
            'category_id' => $category->id,
            'title' => 'University One Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $userTwo->id,
            'category_id' => $category->id,
            'title' => 'University Two Book',
        ]);

    $response = $this->getJson(
        "/api/v1/listings?university_id={$universityOne->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $listing->id)
        ->assertJsonPath('data.0.title', 'University One Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('returns no listings for another university', function () {
    $universityOne = University::factory()->create();
    $universityTwo = University::factory()->create();

    $category = Category::factory()->create();

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($user)->create([
        'university_id' => $universityOne->id,
    ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

    $response = $this->getJson(
        "/api/v1/listings?university_id={$universityTwo->id}"
    );

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('combines university filter with search', function () {
    $universityOne = University::factory()->create();
    $universityTwo = University::factory()->create();

    $category = Category::factory()->create();

    $userOne = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($userOne)->create([
        'university_id' => $universityOne->id,
    ]);

    $userTwo = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($userTwo)->create([
        'university_id' => $universityTwo->id,
    ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $userOne->id,
            'category_id' => $category->id,
            'title' => 'Laravel Database Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $userOne->id,
            'category_id' => $category->id,
            'title' => 'Clean Code Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $userTwo->id,
            'category_id' => $category->id,
            'title' => 'Laravel Laptop',
        ]);

    $response = $this->getJson(
        "/api/v1/listings?university_id={$universityOne->id}&search=Laravel"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Laravel Database Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('combines university and category filters', function () {
    $university = University::factory()->create();

    $books = Category::factory()->create();
    $electronics = Category::factory()->create();

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($user)->create([
        'university_id' => $university->id,
    ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $user->id,
            'category_id' => $books->id,
            'title' => 'Laravel Book',
        ]);

    Listing::factory()
        ->published()
        ->create([
            'user_id' => $user->id,
            'category_id' => $electronics->id,
            'title' => 'Laptop',
        ]);

    $response = $this->getJson(
        "/api/v1/listings?university_id={$university->id}&category_id={$books->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Laravel Book');

    expect($response->json('data'))->toHaveCount(1);
});

it('preserves the university filter during pagination', function () {
    $university = University::factory()->create();
    $category = Category::factory()->create();

    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    Profile::factory()->for($user)->create([
        'university_id' => $university->id,
    ]);

    Listing::factory()
        ->published()
        ->count(16)
        ->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

    $response = $this->getJson(
        "/api/v1/listings?university_id={$university->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.last_page', 2);

    expect($response->json('links.next'))
        ->toContain('university_id=' . $university->id);
});
