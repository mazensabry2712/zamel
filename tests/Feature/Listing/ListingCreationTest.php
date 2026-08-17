<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

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

    $response = $this->postJson('/api/v1/listings', [
        'category_id' => $category->id,
        'title' => 'Database Book',
        'description' => 'Used database book.',
        'price' => 250,
        'condition' => 'good',
    ]);

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
