<?php

namespace App\Actions\Transaction;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateTransaction
{
    public function execute(Reservation $reservation): Transaction
    {
        return DB::transaction(function () use ($reservation): Transaction {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'reservation' => [
                        'Only confirmed reservations can create a transaction.',
                    ],
                ]);
            }

            if ($reservation->expires_at && $reservation->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'reservation' => [
                        'This reservation has expired.',
                    ],
                ]);
            }

            if ($reservation->transaction()->exists()) {
                throw ValidationException::withMessages([
                    'reservation' => [
                        'This reservation already has a transaction.',
                    ],
                ]);
            }

            $listing = $reservation->listing;
            $buyer = $reservation->user;
            $seller = $listing->user;

            $amount = round((float) $listing->price, 2);
            $buyerFee = round($amount * 0.10, 2);
            $sellerFee = round($amount * 0.10, 2);

            return Transaction::create([
                'reservation_id' => $reservation->id,
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'amount' => $amount,
                'platform_buyer_fee' => $buyerFee,
                'platform_seller_fee' => $sellerFee,
                'total_amount' => round($amount + $buyerFee, 2),
                'seller_amount' => round($amount - $sellerFee, 2),
                'status' => 'pending',
                'completed_at' => null,
                'cancelled_at' => null,
            ]);
        });
    }
}
