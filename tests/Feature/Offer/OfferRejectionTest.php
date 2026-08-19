<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createRejectionScenario(string $offerStatus = 'pending', ?\DateTimeInterface $offerExpiresAt = null): array
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

    $offer = Offer::factory()->create([
        'request_id' => $request->id,
        'user_id' => $seller->id,
        'status' => $offerStatus,
        'expires_at' => $offerExpiresAt ?? now()->addWeek(),
    ]);

    return compact('buyer', 'seller', 'otherUser', 'request', 'offer');
}

it('allows the request owner to reject a pending offer', function () {
    $scenario = createRejectionScenario();

    Sanctum::actingAs($scenario['buyer']);

    $response = $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    $this->assertDatabaseHas('offers', [
        'id' => $scenario['offer']->id,
        'status' => 'rejected',
    ]);
});

it('prevents the seller from rejecting their own offer', function () {
    $scenario = createRejectionScenario();

    Sanctum::actingAs($scenario['seller']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertForbidden();
});

it('prevents another user from rejecting an offer', function () {
    $scenario = createRejectionScenario();

    Sanctum::actingAs($scenario['otherUser']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertForbidden();
});

it('prevents rejecting an already accepted offer', function () {
    $scenario = createRejectionScenario('accepted');

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnprocessable();
});

it('prevents rejecting a rejected offer', function () {
    $scenario = createRejectionScenario('rejected');

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnprocessable();
});

it('prevents rejecting a withdrawn offer', function () {
    $scenario = createRejectionScenario('withdrawn');

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnprocessable();
});

it('prevents rejecting an expired offer', function () {
    $scenario = createRejectionScenario('pending', now()->subDay());

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnprocessable();
});

it('prevents rejecting an offer for a closed request', function () {
    $scenario = createRejectionScenario();
    $scenario['request']->update(['status' => 'fulfilled']);

    Sanctum::actingAs($scenario['buyer']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnprocessable();
});

it('requires authentication to reject an offer', function () {
    $scenario = createRejectionScenario();

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/offers/{$scenario['offer']->id}/reject"
    )->assertUnauthorized();
});
