<?php

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createRequestShowScenario(string $status = 'open', ?\DateTimeInterface $expiresAt = null): array
{
    $owner = User::factory()->create(['status' => 'active']);
    $viewer = User::factory()->create(['status' => 'active']);
    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $request = MarketplaceRequest::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'status' => $status,
        'expires_at' => $expiresAt,
    ]);

    return compact('owner', 'viewer', 'category', 'request');
}

it('allows the owner to view a fulfilled request', function () {
    $scenario = createRequestShowScenario('fulfilled');

    Sanctum::actingAs($scenario['owner']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['request']->id)
        ->assertJsonPath('data.status', 'fulfilled');
});

it('allows the owner to view an expired request', function () {
    $scenario = createRequestShowScenario('open', now()->subDay());

    Sanctum::actingAs($scenario['owner']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['request']->id)
        ->assertJsonPath('data.status', 'open');
});

it('allows another authenticated user to view a public open request', function () {
    $scenario = createRequestShowScenario('open', now()->addDay());

    Sanctum::actingAs($scenario['viewer']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $scenario['request']->id)
        ->assertJsonPath('data.status', 'open');
});

it('prevents another authenticated user from viewing a fulfilled request', function () {
    $scenario = createRequestShowScenario('fulfilled');

    Sanctum::actingAs($scenario['viewer']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertForbidden();
});

it('prevents another authenticated user from viewing a cancelled request', function () {
    $scenario = createRequestShowScenario('cancelled');

    Sanctum::actingAs($scenario['viewer']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertForbidden();
});

it('prevents another authenticated user from viewing an expired request', function () {
    $scenario = createRequestShowScenario('open', now()->subDay());

    Sanctum::actingAs($scenario['viewer']);

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertForbidden();
});

it('requires authentication to view a request', function () {
    $scenario = createRequestShowScenario('open', now()->addDay());

    $this->getJson("/api/v1/requests/{$scenario['request']->id}")
        ->assertUnauthorized();
});
