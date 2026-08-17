<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function listingPayload(int $categoryId): array
{
    return [
        'category_id' => $categoryId,
        'title' => 'Database Book',
        'description' => 'Used database book.',
        'price' => 250,
        'condition' => 'good',
    ];
}

it('allows an active user to create a listing using an approved category', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        '/api/v1/listings',
        listingPayload($category->id),
    );

    $response
        ->assertStatus(201)
        ->assertJsonPath('data.title', 'Database Book')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.moderation.status', 'pending');

    $this->assertDatabaseHas('listings', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);
});

it('prevents unauthenticated users from creating listings', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->postJson(
        '/api/v1/listings',
        listingPayload($category->id),
    );

    $response->assertStatus(401);

    $this->assertDatabaseMissing('listings', [
        'category_id' => $category->id,
        'title' => 'Database Book',
    ]);
});

it('prevents creating a listing with a pending category', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'pending',
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        '/api/v1/listings',
        listingPayload($category->id),
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);

    $this->assertDatabaseMissing('listings', [
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
});

it('prevents creating a listing with a rejected category', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'rejected',
        'is_active' => false,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        '/api/v1/listings',
        listingPayload($category->id),
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);

    $this->assertDatabaseMissing('listings', [
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
});

it('prevents creating a listing with an inactive category', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => false,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        '/api/v1/listings',
        listingPayload($category->id),
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);

    $this->assertDatabaseMissing('listings', [
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
});
