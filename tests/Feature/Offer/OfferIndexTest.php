<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createOfferIndexScenario(): array
{
    $buyer = User::factory()->create([
        'status' => 'active',
    ]);

    $sellerOne = User::factory()->create([
        'status' => 'active',
    ]);

    $sellerTwo = User::factory()->create([
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

    $pendingOffer = Offer::factory()->pending()->create([
        'request_id' => $request->id,
        'user_id' => $sellerOne->id,
        'price' => 250,
    ]);

    $acceptedOffer = Offer::factory()->accepted()->create([
        'request_id' => $request->id,
        'user_id' => $sellerTwo->id,
        'price' => 300,
    ]);

    return compact(
        'buyer',
        'sellerOne',
        'sellerTwo',
        'category',
        'request',
        'pendingOffer',
        'acceptedOffer',
    );
}

it('allows the request owner to list offers for their request', function () {
    $scenario = createOfferIndexScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers"
    );

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('prevents another user from listing offers for a request they do not own', function () {
    $scenario = createOfferIndexScenario();

    $otherUser = User::factory()->create([
        'status' => 'active',
    ]);

    Sanctum::actingAs($otherUser);

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers"
    )->assertForbidden();
});

it('prevents an offer seller from listing all offers on another users request', function () {
    $scenario = createOfferIndexScenario();

    Sanctum::actingAs($scenario['sellerOne']);

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers"
    )->assertForbidden();
});

it('supports filtering offers by status', function () {
    $scenario = createOfferIndexScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers?status=pending"
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $scenario['pendingOffer']->id)
        ->assertJsonPath('data.0.status', 'pending');
});

it('paginates the offers collection', function () {
    $scenario = createOfferIndexScenario();

    Offer::factory()->count(12)->pending()->create([
        'request_id' => $scenario['request']->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers?per_page=5"
    );

    $response
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 1);
});

it('requires authentication to list request offers', function () {
    $scenario = createOfferIndexScenario();

    $this->getJson(
        "/api/v1/requests/{$scenario['request']->id}/offers"
    )->assertUnauthorized();
});
