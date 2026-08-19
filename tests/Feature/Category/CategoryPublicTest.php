<?php

use App\Models\Category;

it('lists only approved active categories ordered by name', function () {
    Category::factory()->create([
        'name' => 'Z Books',
        'slug' => 'z-books',
        'status' => 'approved',
        'is_active' => true,
    ]);

    Category::factory()->create([
        'name' => 'A Calculators',
        'slug' => 'a-calculators',
        'status' => 'approved',
        'is_active' => true,
    ]);

    Category::factory()->pending()->create([
        'name' => 'Pending Category',
        'slug' => 'pending-category',
    ]);

    Category::factory()->create([
        'name' => 'Inactive Category',
        'slug' => 'inactive-category',
        'status' => 'approved',
        'is_active' => false,
    ]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'A Calculators')
        ->assertJsonPath('data.1.name', 'Z Books')
        ->assertJsonMissing(['name' => 'Pending Category'])
        ->assertJsonMissing(['name' => 'Inactive Category']);
});

it('returns an empty category collection when no active categories exist', function () {
    Category::factory()->pending()->create();

    Category::factory()->create([
        'status' => 'approved',
        'is_active' => false,
    ]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('shows an approved active category', function () {
    $category = Category::factory()->create([
        'name' => 'Books',
        'slug' => 'books',
        'description' => 'Used books',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.name', 'Books')
        ->assertJsonPath('data.slug', 'books')
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.is_active', true);
});

it('does not expose a pending category publicly', function () {
    $category = Category::factory()->pending()->create();

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertNotFound();
});

it('does not expose an inactive approved category publicly', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => false,
    ]);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertNotFound();
});

it('returns not found for a non existent category', function () {
    $this->getJson('/api/v1/categories/999999')
        ->assertNotFound();
});

it('does not require authentication for public category endpoints', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $this->getJson('/api/v1/categories')
        ->assertOk();

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertOk();
});
