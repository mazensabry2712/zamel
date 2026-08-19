<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createWithdrawalScenario(string $offerStatus = 'pending', ?\DateTimeInterface $offerExpiresAt = null): array
{
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

    $offer = Offer::factory()->create([
        'request_id' => $request->id,
        'user_id' => $seller->id,
        'status' => $offerStatus,
        'expires_at' => $offerExpiresAt,
    ]);

    return compact('buyer', 'seller', 'request', 'offer');
}

it('allows the seller to withdraw their pending offer', function () {
    $scenario = createWithdrawalScenario();

    Sanctum::actingAs($scenario['seller']);

    $response = $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'withdrawn');

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['offer']->id,
        'status' => 'withdrawn',
    ]);
});

it('prevents the buyer from withdrawing an offer', function () {
    $scenario = createWithdrawalScenario();

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertForbidden();
});

it('prevents another user from withdrawing an offer', function () {
    $scenario = createWithdrawalScenario();
    $otherUser = User::factory()->create([
        'status' => 'active',
    ]);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertForbidden();
});

it('prevents withdrawing an already accepted offer', function () {
    $scenario = createWithdrawalScenario('accepted');

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnprocessable();
});

it('prevents withdrawing a rejected offer', function () {
    $scenario = createWithdrawalScenario('rejected');

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnprocessable();
});

it('prevents withdrawing an already withdrawn offer', function () {
    $scenario = createWithdrawalScenario('withdrawn');

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnprocessable();
});

it('prevents withdrawing an expired offer', function () {
    $scenario = createWithdrawalScenario('pending', now()->subDay());

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnprocessable();
});

it('prevents withdrawing an offer for a closed request', function () {
    $scenario = createWithdrawalScenario();
    $scenario['request']->update([
        'status' => 'fulfilled',
    ]);

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnprocessable();
});

it('requires authentication to withdraw an offer', function () {
    $scenario = createWithdrawalScenario();

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/withdraw"
    )->assertUnauthorized();
});
