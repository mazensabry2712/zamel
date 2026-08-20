<?php

use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows the buyer to view their transaction', function () {
    $transaction = Transaction::factory()->create();

    Sanctum::actingAs($transaction->buyer);

    $response = $this->getJson(
        "/api/v1/transactions/{$transaction->id}"
    );

    $response->assertOk()
        ->assertJsonPath('data.id', $transaction->id)
        ->assertJsonPath('data.reservation_id', $transaction->reservation_id)
        ->assertJsonPath('data.listing_id', $transaction->listing_id)
        ->assertJsonPath('data.status', $transaction->status);
});

it('allows the seller to view their transaction', function () {
    $transaction = Transaction::factory()->create();

    Sanctum::actingAs($transaction->seller);

    $response = $this->getJson(
        "/api/v1/transactions/{$transaction->id}"
    );

    $response->assertOk()
        ->assertJsonPath('data.id', $transaction->id);
});

it('prevents another user from viewing the transaction', function () {
    $transaction = Transaction::factory()->create();

    $otherUser = User::factory()->create();

    Sanctum::actingAs($otherUser);

    $response = $this->getJson(
        "/api/v1/transactions/{$transaction->id}"
    );

    $response->assertForbidden();
});

it('requires authentication to view a transaction', function () {
    $transaction = Transaction::factory()->create();

    $response = $this->getJson(
        "/api/v1/transactions/{$transaction->id}"
    );

    $response->assertUnauthorized();
});

it('does not expose buyer or seller personal information', function () {
    $transaction = Transaction::factory()->create();

    Sanctum::actingAs($transaction->buyer);

    $response = $this->getJson(
        "/api/v1/transactions/{$transaction->id}"
    );

    $response->assertOk()
        ->assertJsonMissing([
            'email' => $transaction->seller->email,
        ])
        ->assertJsonMissing([
            'email' => $transaction->buyer->email,
        ])
        ->assertJsonMissing([
            'phone' => $transaction->seller->phone,
        ])
        ->assertJsonMissing([
            'phone' => $transaction->buyer->phone,
        ]);
});
