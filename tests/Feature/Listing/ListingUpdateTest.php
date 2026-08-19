<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createListingUpdateScenario(): array
{
    $owner = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $otherUser = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $newCategory = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Database Book',
        'description' => 'Old description',
        'price' => 250,
        'condition' => 'good',
        'status' => 'draft',
        'moderation_status' => 'pending',
    ]);

    return compact('owner', 'otherUser', 'category', 'newCategory', 'listing');
}

it('allows the listing owner to update their listing', function () {
    $scenario = createListingUpdateScenario();

    Sanctum::actingAs($scenario['owner']);

    $response = $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        [
            'category_id' => $scenario['newCategory']->id,
            'title' => 'Updated Database Book',
            'description' => 'Updated description',
            'price' => 300,
            'condition' => 'like_new',
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['listing']->id)
        ->assertJsonPath('data.title', 'Updated Database Book')
        ->assertJsonPath('data.category_id', $scenario['newCategory']->id)
        ->assertJsonPath('data.price', '300.00')
        ->assertJsonPath('data.condition', 'like_new')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.moderation.status', 'pending');

    $this->assertDatabaseHas('listings', [
        'id' => $scenario['listing']->id,
        'user_id' => $scenario['owner']->id,
        'category_id' => $scenario['newCategory']->id,
        'title' => 'Updated Database Book',
        'price' => 300,
        'condition' => 'like_new',
    ]);
});

it('prevents another user from updating a listing', function () {
    $scenario = createListingUpdateScenario();

    Sanctum::actingAs($scenario['otherUser']);

    $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        ['title' => 'Unauthorized Update'],
    )->assertForbidden();
});

it('requires authentication to update a listing', function () {
    $scenario = createListingUpdateScenario();

    $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        ['title' => 'Unauthorized Update'],
    )->assertUnauthorized();
});

it('prevents updating a listing with an unavailable category', function () {
    $scenario = createListingUpdateScenario();

    $pendingCategory = Category::factory()->create([
        'status' => 'pending',
        'is_active' => true,
    ]);

    Sanctum::actingAs($scenario['owner']);

    $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        ['category_id' => $pendingCategory->id],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

it('validates listing update fields', function () {
    $scenario = createListingUpdateScenario();

    Sanctum::actingAs($scenario['owner']);

    $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        [
            'category_id' => 999999,
            'title' => 'x',
            'price' => -10,
            'condition' => 'broken',
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'category_id',
            'title',
            'price',
            'condition',
        ]);
});

it('moves a published listing back to draft and pending moderation after update', function () {
    $scenario = createListingUpdateScenario();

    $scenario['listing']->update([
        'status' => 'published',
        'moderation_status' => 'approved',
        'moderation_reason' => 'Approved by admin',
        'moderated_at' => now(),
        'published_at' => now(),
    ]);

    Sanctum::actingAs($scenario['owner']);

    $this->putJson(
        "/api/v1/listings/{$scenario['listing']->id}",
        ['title' => 'Changed Published Listing'],
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.moderation.status', 'pending');

    $this->assertDatabaseHas('listings', [
        'id' => $scenario['listing']->id,
        'status' => 'draft',
        'moderation_status' => 'pending',
        'moderation_reason' => null,
        'moderated_at' => null,
        'published_at' => null,
    ]);
});
