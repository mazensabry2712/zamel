<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an active user to create an offer on an open request', function () {
    $buyer = User::factory()->create([
        'status' => 'active',
    ]);

    $seller = User::factory()->create([
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $request = MarketplaceRequest::factory()->create([
        'user_id' => $buyer->id,
        'category_id' => $category->id,
        'status' => 'open',
        'expires_at' => now()->addWeek(),
    ]);

    Sanctum::actingAs($seller);

    $response = $this->postJson("/api/v1/requests/{$request->id}/offers", [
        'price' => 280,
        'condition' => 'good',
        'message' => 'The book is available and in good condition.',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.price', '280.00')
        ->assertJsonPath('data.condition', 'good')
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('offers', [
        'request_id' => $request->id,
        'user_id' => $seller->id,
        'price' => 280,
        'condition' => 'good',
        'status' => 'pending',
    ]);
});
