<?php

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a transaction with consistent reservation, listing, buyer and seller relationships', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->reservation_id)
        ->toBe($transaction->reservation->id);

    expect($transaction->listing_id)
        ->toBe($transaction->reservation->listing_id)
        ->toBe($transaction->listing->id);

    expect($transaction->buyer_id)
        ->toBe($transaction->reservation->user_id)
        ->toBe($transaction->buyer->id);

    expect($transaction->seller_id)
        ->toBe($transaction->listing->user_id)
        ->toBe($transaction->seller->id);
});

it('uses the listing price as the transaction amount and calculates platform fees correctly', function () {
    $transaction = Transaction::factory()->create();

    $amount = (float) $transaction->listing->price;
    $buyerFee = round($amount * 0.10, 2);
    $sellerFee = round($amount * 0.10, 2);

    expect((float) $transaction->amount)
        ->toBe($amount);

    expect((float) $transaction->platform_buyer_fee)
        ->toBe($buyerFee);

    expect((float) $transaction->platform_seller_fee)
        ->toBe($sellerFee);

    expect((float) $transaction->total_amount)
        ->toBe($amount + $buyerFee);

    expect((float) $transaction->seller_amount)
        ->toBe($amount - $sellerFee);
});

it('creates transactions with pending status by default', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->status)->toBe('pending');
    expect($transaction->completed_at)->toBeNull();
    expect($transaction->cancelled_at)->toBeNull();
});

it('supports transaction lifecycle factory states', function () {
    $paid = Transaction::factory()->paid()->create();
    $inDelivery = Transaction::factory()->inDelivery()->create();
    $delivered = Transaction::factory()->delivered()->create();
    $completed = Transaction::factory()->completed()->create();
    $cancelled = Transaction::factory()->cancelled()->create();
    $refunded = Transaction::factory()->refunded()->create();

    expect($paid->status)->toBe('paid');
    expect($inDelivery->status)->toBe('in_delivery');
    expect($delivered->status)->toBe('delivered');
    expect($completed->status)->toBe('completed');
    expect($completed->completed_at)->not->toBeNull();
    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->cancelled_at)->not->toBeNull();
    expect($refunded->status)->toBe('refunded');
});
