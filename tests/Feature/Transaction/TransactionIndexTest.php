<?php

use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists transactions where the authenticated user is buyer or seller', function () {
    $transaction = Transaction::factory()->create();
    $buyerTransaction = Transaction::factory()->create([
        'buyer_id' => $transaction->buyer_id,
    ]);
    $sellerTransaction = Transaction::factory()->create([
        'seller_id' => $transaction->seller_id,
    ]);

    Transaction::factory()->create();

    Sanctum::actingAs($transaction->buyer);

    $response = $this->getJson('/api/v1/transactions');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $transaction->id])
        ->assertJsonFragment(['id' => $buyerTransaction->id])
        ->assertJsonMissing(['id' => $sellerTransaction->id]);
});

it('includes transactions where the authenticated user is the seller', function () {
    $transaction = Transaction::factory()->create();
    $otherTransaction = Transaction::factory()->create([
        'seller_id' => $transaction->seller_id,
    ]);

    Sanctum::actingAs($transaction->seller);

    $response = $this->getJson('/api/v1/transactions');

    $response->assertOk()
        ->assertJsonFragment(['id' => $transaction->id])
        ->assertJsonFragment(['id' => $otherTransaction->id]);
});

it('can filter transactions by status', function () {
    $transaction = Transaction::factory()->create();
    Transaction::factory()->completed()->create([
        'buyer_id' => $transaction->buyer_id,
        'seller_id' => $transaction->seller_id,
    ]);
    Transaction::factory()->cancelled()->create([
        'buyer_id' => $transaction->buyer_id,
        'seller_id' => $transaction->seller_id,
    ]);

    Sanctum::actingAs($transaction->buyer);

    $response = $this->getJson('/api/v1/transactions?status=completed');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'completed');
});

it('paginates transactions', function () {
    $transaction = Transaction::factory()->create();

    Transaction::factory()->count(2)->create([
        'buyer_id' => $transaction->buyer_id,
        'seller_id' => $transaction->seller_id,
    ]);

    Sanctum::actingAs($transaction->buyer);

    $response = $this->getJson('/api/v1/transactions?per_page=2');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3);
});

it('requires authentication to list transactions', function () {
    $response = $this->getJson('/api/v1/transactions');

    $response->assertUnauthorized();
});

it('does not expose transactions belonging only to another user', function () {
    $transaction = Transaction::factory()->create();
    $otherUser = User::factory()->create();

    Sanctum::actingAs($otherUser);

    $response = $this->getJson('/api/v1/transactions');

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});
