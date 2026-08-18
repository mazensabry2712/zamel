<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an active user to create a marketplace request', function () {
    $user = User::factory()->create(['status' => 'active']);
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
        ->assertJsonPath('data.category_id', $category->id)
        ->assertJsonPath('data.status', 'open');

    $this->assertDatabaseHas('marketplace_requests', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'status' => 'open',
    ]);
});

it('requires authentication to create a request', function () {
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/requests', [
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'description' => 'Need this book.',
        'budget' => 350,
    ])->assertUnauthorized();
});

it('prevents creating a request with an unavailable category', function () {
    $user = User::factory()->create(['status' => 'active']);
    $category = Category::factory()->pending()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/requests', [
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'description' => 'Need this book.',
        'budget' => 350,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

it('validates request creation fields', function () {
    $user = User::factory()->create(['status' => 'active']);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/requests', [
        'category_id' => 999999,
        'title' => 'x',
        'description' => '',
        'budget' => -10,
        'expires_at' => now()->subDay()->toISOString(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'category_id',
            'title',
            'description',
            'budget',
            'expires_at',
        ]);
});

it('does not allow the client to control status or user id', function () {
    $user = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/requests', [
        'user_id' => $otherUser->id,
        'category_id' => $category->id,
        'title' => 'Database Systems Book',
        'description' => 'Need this book.',
        'budget' => 350,
        'status' => 'fulfilled',
    ]);

    $response->assertCreated();

    $request = MarketplaceRequest::query()->latest('id')->firstOrFail();

    expect($request->user_id)->toBe($user->id)
        ->and($request->status)->toBe('open');
});

it('lists only open non expired requests', function () {
    $viewer = User::factory()->create(['status' => 'active']);
    Sanctum::actingAs($viewer);

    $open = MarketplaceRequest::factory()->open()->create([
        'title' => 'Open Book Request',
    ]);

    MarketplaceRequest::factory()->fulfilled()->create([
        'title' => 'Fulfilled Request',
    ]);

    MarketplaceRequest::factory()->expired()->create([
        'title' => 'Expired Request',
    ]);

    $response = $this->getJson('/api/v1/requests');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $open->id)
        ->assertJsonPath('data.0.title', 'Open Book Request');

    expect($response->json('data'))->toHaveCount(1);
});

it('allows an authenticated user to view an open request', function () {
    $viewer = User::factory()->create(['status' => 'active']);
    $request = MarketplaceRequest::factory()->open()->create();

    Sanctum::actingAs($viewer);

    $this->getJson("/api/v1/requests/{$request->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $request->id);
});

it('allows the owner to update an open request', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $request = MarketplaceRequest::factory()->open()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $this->putJson("/api/v1/requests/{$request->id}", [
        'category_id' => $category->id,
        'title' => 'Updated Request',
        'budget' => 500,
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Request')
        ->assertJsonPath('data.category_id', $category->id)
        ->assertJsonPath('data.budget', '500.00');
});

it('prevents another user from updating a request', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);
    $request = MarketplaceRequest::factory()->open()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($otherUser);

    $this->putJson("/api/v1/requests/{$request->id}", [
        'title' => 'Unauthorized Update',
    ])->assertForbidden();
});

it('allows the owner to delete an open request', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $request = MarketplaceRequest::factory()->open()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/v1/requests/{$request->id}")
        ->assertOk();

    $this->assertDatabaseMissing('marketplace_requests', [
        'id' => $request->id,
    ]);
});

it('prevents another user from deleting a request', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);
    $request = MarketplaceRequest::factory()->open()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($otherUser);

    $this->deleteJson("/api/v1/requests/{$request->id}")
        ->assertForbidden();
});

it('does not allow updating a fulfilled request', function () {
    $owner = User::factory()->create(['status' => 'active']);
    $request = MarketplaceRequest::factory()->fulfilled()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $this->putJson("/api/v1/requests/{$request->id}", [
        'title' => 'Cannot Update',
    ])->assertForbidden();
});
