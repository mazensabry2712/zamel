<?php

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteTransaction
{
    public function execute(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction): Transaction {
            $transaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if (in_array($transaction->status, ['completed', 'cancelled', 'refunded'], true)) {
                throw ValidationException::withMessages([
                    'transaction' => [
                        'This transaction cannot be completed in its current status.',
                    ],
                ]);
            }

            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'cancelled_at' => null,
            ]);

            return $transaction->refresh();
        });
    }
}
