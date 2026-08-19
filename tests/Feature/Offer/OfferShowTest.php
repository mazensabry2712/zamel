<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createOfferShowScenario(): array
{
    $buyer = User::factory()->create(['status' => 'active']);
    $seller = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);

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

    $offer = Offer::factory()->pending()->create([
        'request_id' => $request->id,
        'user_id' => $seller->id,
        'price' => 280,
        'condition' => 'good',
        'message' => 'The book is available and in good condition.',
    ]);

    return compact('buyer', 'seller', 'otherUser', 'category', 'request', 'offer');
}

it('allows the request owner to view an offer', function () {
    $scenario = createOfferShowScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['offer']->id)
        ->assertJsonPath('data.request_id', $scenario['request']->id)
        ->assertJsonPath('data.user_id', $scenario['seller']->id)
        ->assertJsonPath('data.price', '280.00')
        ->assertJsonPath('data.condition', 'good')
        ->assertJsonPath('data.status', 'pending');
});

it('allows the offer seller to view their offer', function () {
    $scenario = createOfferShowScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}"
    )
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['offer']->id);
});

it('prevents another user from viewing an offer they are not involved with', function () {
    $scenario = createOfferShowScenario();

    Sanctum::actingAs($scenario['otherUser']);

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}"
    )->assertForbidden();
});

it('requires authentication to view an offer', function () {
    $scenario = createOfferShowScenario();

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}"
    )->assertUnauthorized();
});

it('returns not found when the offer belongs to another request', function () {
    $scenario = createOfferShowScenario();

    $anotherRequest = MarketplaceRequest::factory()->create([
        'user_id' => $scenario['buyer']->id,
        'category_id' => $scenario['category']->id,
        'status' => 'open',
        'expires_at' => now()->addWeek(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->getJson(
        "/api/v1/requests/{$anotherRequest->id}/offers/{$scenario['offer']->id}"
    )->assertNotFound();
});

it('prevents viewing an offer on a non-public request unless the user is the request owner or seller', function () {
    $scenario = createOfferShowScenario();

    $scenario['request']->update(['status' => 'fulfilled']);

    Sanctum::actingAs($scenario['otherUser']);

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}"
    )->assertForbidden();
});
