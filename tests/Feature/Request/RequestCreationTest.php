<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an active user to create a marketplace request', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/requests', [
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'description' => 'I need the 2025 edition in good condition.',
        'budget' => 350,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.title', 'Database Systems Book')
        ->assertJsonPath('data.category_id', $category->id);

    $this->assertDatabaseHas('marketplace_requests', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'status' => 'open',
    ]);
});
