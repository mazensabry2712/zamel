<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createAcceptanceScenario(): array
{
    $buyer = User::factory()->create(['status' => 'active']);
    $seller = User::factory()->create(['status' => 'active']);
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
        'expires_at' => now()->addDay(),
    ]);

    return compact('buyer', 'seller', 'request', 'offer');
}

it('allows the request owner to accept a pending offer', function () {
    $scenario = createAcceptanceScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'accepted');

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['offer']->id,
        'status' => 'accepted',
    ]);
});

it('prevents the seller from accepting their own offer', function () {
    $scenario = createAcceptanceScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertForbidden();
});

it('prevents another user from accepting an offer', function () {
    $scenario = createAcceptanceScenario();
    $otherUser = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($otherUser);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertForbidden();
});

it('prevents accepting an already accepted offer', function () {
    $scenario = createAcceptanceScenario();
    $scenario['offer']->update(['status' => 'accepted']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnprocessable();
});

it('prevents accepting a rejected offer', function () {
    $scenario = createAcceptanceScenario();
    $scenario['offer']->update(['status' => 'rejected']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnprocessable();
});

it('prevents accepting a withdrawn offer', function () {
    $scenario = createAcceptanceScenario();
    $scenario['offer']->update(['status' => 'withdrawn']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnprocessable();
});

it('prevents accepting an expired offer', function () {
    $scenario = createAcceptanceScenario();
    $scenario['offer']->update([
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnprocessable();
});

it('prevents accepting an offer for a closed request', function () {
    $scenario = createAcceptanceScenario();
    $scenario['request']->update(['status' => 'fulfilled']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnprocessable();
});

it('requires authentication to accept an offer', function () {
    $scenario = createAcceptanceScenario();

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/accept"
    )->assertUnauthorized();
});
