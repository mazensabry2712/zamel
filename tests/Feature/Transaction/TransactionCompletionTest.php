<?php

use App\Actions\Transaction\CompleteTransaction;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('completes a pending transaction', function () {
    $transaction = Transaction::factory()->create([
        'status' => 'pending',
        'completed_at' => null,
    ]);

    $completed = app(CompleteTransaction::class)->execute($transaction);

    expect($completed->status)->toBe('completed');
    expect($completed->completed_at)->not->toBeNull();
    expect($completed->cancelled_at)->toBeNull();

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => 'completed',
    ]);
});

it('completes a paid transaction', function () {
    $transaction = Transaction::factory()->paid()->create();

    $completed = app(CompleteTransaction::class)->execute($transaction);

    expect($completed->status)->toBe('completed');
    expect($completed->completed_at)->not->toBeNull();
});

it('completes an in delivery transaction', function () {
    $transaction = Transaction::factory()->inDelivery()->create();

    $completed = app(CompleteTransaction::class)->execute($transaction);

    expect($completed->status)->toBe('completed');
});

it('completes a delivered transaction', function () {
    $transaction = Transaction::factory()->delivered()->create();

    $completed = app(CompleteTransaction::class)->execute($transaction);

    expect($completed->status)->toBe('completed');
});

it('cannot complete an already completed transaction', function () {
    $transaction = Transaction::factory()->completed()->create();

    expect(fn () => app(CompleteTransaction::class)->execute($transaction))
        ->toThrow(ValidationException::class);
});

it('cannot complete a cancelled transaction', function () {
    $transaction = Transaction::factory()->cancelled()->create();

    expect(fn () => app(CompleteTransaction::class)->execute($transaction))
        ->toThrow(ValidationException::class);
});

it('cannot complete a refunded transaction', function () {
    $transaction = Transaction::factory()->refunded()->create();

    expect(fn () => app(CompleteTransaction::class)->execute($transaction))
        ->toThrow(ValidationException::class);
});
