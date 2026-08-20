<?php

use App\Actions\Transaction\CreateTransaction;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createTransactionScenario(): array
{
    $seller = User::factory()->create(['status' => 'active']);
    $buyer = User::factory()->create(['status' => 'active']);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    $listing = Listing::factory()->published()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'price' => 1000.00,
    ]);

    $reservation = Reservation::factory()->confirmed()->create([
        'listing_id' => $listing->id,
        'user_id' => $buyer->id,
        'expires_at' => now()->addDay(),
    ]);

    return compact('seller', 'buyer', 'category', 'listing', 'reservation');
}

it('creates a transaction from a confirmed reservation', function () {
    $scenario = createTransactionScenario();

    $transaction = app(CreateTransaction::class)->execute($scenario['reservation']);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->reservation_id)->toBe($scenario['reservation']->id);
    expect($transaction->listing_id)->toBe($scenario['listing']->id);
    expect($transaction->buyer_id)->toBe($scenario['buyer']->id);
    expect($transaction->seller_id)->toBe($scenario['seller']->id);
    expect((float) $transaction->amount)->toBe(1000.00);
    expect((float) $transaction->platform_buyer_fee)->toBe(100.00);
    expect((float) $transaction->platform_seller_fee)->toBe(100.00);
    expect((float) $transaction->total_amount)->toBe(1100.00);
    expect((float) $transaction->seller_amount)->toBe(900.00);
    expect($transaction->status)->toBe('pending');
});

it('rejects transaction creation for a pending reservation', function () {
    $scenario = createTransactionScenario();
    $scenario['reservation']->update([
        'status' => 'pending',
        'confirmed_at' => null,
    ]);

    expect(fn () => app(CreateTransaction::class)->execute($scenario['reservation']))
        ->toThrow(ValidationException::class);

    expect(Transaction::count())->toBe(0);
});

it('rejects transaction creation for a cancelled reservation', function () {
    $scenario = createTransactionScenario();
    $scenario['reservation']->update([
        'status' => 'cancelled',
        'cancelled_at' => now(),
    ]);

    expect(fn () => app(CreateTransaction::class)->execute($scenario['reservation']))
        ->toThrow(ValidationException::class);

    expect(Transaction::count())->toBe(0);
});

it('rejects transaction creation for an expired reservation', function () {
    $scenario = createTransactionScenario();
    $scenario['reservation']->update([
        'expires_at' => now()->subDay(),
    ]);

    expect(fn () => app(CreateTransaction::class)->execute($scenario['reservation']))
        ->toThrow(ValidationException::class);

    expect(Transaction::count())->toBe(0);
});

it('rejects creating a second transaction for the same reservation', function () {
    $scenario = createTransactionScenario();

    $firstTransaction = app(CreateTransaction::class)->execute($scenario['reservation']);

    expect(fn () => app(CreateTransaction::class)->execute($scenario['reservation']))
        ->toThrow(ValidationException::class);

    expect(Transaction::count())->toBe(1);
    expect(Transaction::first()->id)->toBe($firstTransaction->id);
});

it('derives buyer seller listing and amount from the reservation instead of caller input', function () {
    $scenario = createTransactionScenario();

    $transaction = app(CreateTransaction::class)->execute($scenario['reservation']);

    expect($transaction->buyer_id)->toBe($scenario['reservation']->user_id);
    expect($transaction->seller_id)->toBe($scenario['listing']->user_id);
    expect($transaction->listing_id)->toBe($scenario['reservation']->listing_id);
    expect((float) $transaction->amount)->toBe((float) $scenario['listing']->price);
});
