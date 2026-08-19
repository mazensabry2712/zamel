<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createFulfillmentScenario(): array
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

    $acceptedOffer = Offer::factory()->pending()->create([
        'request_id' => $request->id,
        'user_id' => $sellerOne->id,
        'expires_at' => now()->addWeek(),
    ]);

    $pendingOffer = Offer::factory()->pending()->create([
        'request_id' => $request->id,
        'user_id' => $sellerTwo->id,
        'expires_at' => now()->addWeek(),
    ]);

    return compact(
        'buyer',
        'sellerOne',
        'sellerTwo',
        'request',
        'acceptedOffer',
        'pendingOffer',
    );
}

it('fulfills the request when the request owner accepts an offer', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    );

    $response->assertOk();

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['acceptedOffer']->id,
        'status' => 'accepted',
    ]);

    $this->assertDatabaseHas('marketplace_requests', [
        'id' => $scenario['request']->id,
        'status' => 'fulfilled',
    ]);
});

it('rejects all remaining pending offers when one offer is accepted', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    )->assertOk();

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['acceptedOffer']->id,
        'status' => 'accepted',
    ]);

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['pendingOffer']->id,
        'status' => 'rejected',
    ]);
});

it('does not change an already rejected offer when another offer is accepted', function () {
    $scenario = createFulfillmentScenario();

    $rejectedOffer = Offer::factory()->rejected()->create([
        'request_id' => $scenario['request']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    )->assertOk();

    $this->assertDatabaseHas('offers', [
        'id' => $rejectedOffer->id,
        'status' => 'rejected',
    ]);
});

it('does not change an already withdrawn offer when another offer is accepted', function () {
    $scenario = createFulfillmentScenario();

    $withdrawnOffer = Offer::factory()->withdrawn()->create([
        'request_id' => $scenario['request']->id,
        'user_id' => User::factory()->create(['status' => 'active'])->id,
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    )->assertOk();

    $this->assertDatabaseHas('offers', [
        'id' => $withdrawnOffer->id,
        'status' => 'withdrawn',
    ]);
});

it('does not allow accepting another pending offer after the request is fulfilled', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    )->assertOk();

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['pendingOffer']->id}/accept"
    )->assertUnprocessable();

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['pendingOffer']->id,
        'status' => 'rejected',
    ]);
});

it('keeps the accepted offer accepted after the request is fulfilled', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/accept"
    )->assertOk();

    $offer = $scenario['acceptedOffer']->fresh();

    expect($offer->status)->toBe('accepted');
});

it('does not fulfill the request when an offer is rejected', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/reject"
    )->assertOk();

    $this->assertDatabaseHas('marketplace_requests', [
        'id' => $scenario['request']->id,
        'status' => 'open',
    ]);
});

it('does not fulfill the request when an offer is withdrawn', function () {
    $scenario = createFulfillmentScenario();

    Sanctum::actingAs($scenario['sellerOne']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['acceptedOffer']->id}/withdraw"
    )->assertOk();

    $this->assertDatabaseHas('marketplace_requests', [
        'id' => $scenario['request']->id,
        'status' => 'open',
    ]);
});
