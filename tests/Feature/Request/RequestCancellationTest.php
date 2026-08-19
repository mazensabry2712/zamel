<?php

use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createRequestCancellationScenario(): array
{
    $owner = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);

    $request = MarketplaceRequest::factory()->open()->create([
        'user_id' => $owner->id,
    ]);

    return compact('owner', 'otherUser', 'request');
}

it('allows the request owner to cancel an open request', function () {
    $scenario = createRequestCancellationScenario();

    Sanctum::actingAs($scenario['owner']);

    $response = $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['request']->id)
        ->assertJsonPath('data.status', 'cancelled');

    $this->assertDatabaseHas('marketplace_requests', [
        'id' => $scenario['request']->id,
        'status' => 'cancelled',
    ]);
});

it('prevents another user from cancelling the request', function () {
    $scenario = createRequestCancellationScenario();

    Sanctum::actingAs($scenario['otherUser']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    )->assertForbidden();

    $this->assertDatabaseHas('marketplace_requests', [
        'id' => $scenario['request']->id,
        'status' => 'open',
    ]);
});

it('requires authentication to cancel a request', function () {
    $scenario = createRequestCancellationScenario();

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    )->assertUnauthorized();
});

it('prevents cancelling a fulfilled request', function () {
    $scenario = createRequestCancellationScenario();
    $scenario['request']->update(['status' => 'fulfilled']);

    Sanctum::actingAs($scenario['owner']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    )->assertUnprocessable();
});

it('prevents cancelling an already cancelled request', function () {
    $scenario = createRequestCancellationScenario();
    $scenario['request']->update(['status' => 'cancelled']);

    Sanctum::actingAs($scenario['owner']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    )->assertUnprocessable();
});

it('prevents cancelling an expired request', function () {
    $scenario = createRequestCancellationScenario();
    $scenario['request']->update([
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($scenario['owner']);

    $this->postJson(
        "/api/v1/requests/{$scenario['request']->id}/cancel"
    )->assertUnprocessable();
});

it('returns not found when cancelling a non existent request', function () {
    $user = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/requests/999999/cancel')
        ->assertNotFound();
});
