<?php

namespace App\Actions\Reservation;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReservation
{
    public function execute(
        User $user,
        Listing $listing,
    ): Reservation {
        if ($listing->status !== 'published') {
            throw ValidationException::withMessages([
                'listing' => [
                    'Only published listings can be reserved.',
                ],
            ]);
        }

        if ($listing->moderation_status !== 'approved') {
            throw ValidationException::withMessages([
                'listing' => [
                    'Only approved listings can be reserved.',
                ],
            ]);
        }

        if ($listing->user_id === $user->id) {
            throw ValidationException::withMessages([
                'listing' => [
                    'You cannot reserve your own listing.',
                ],
            ]);
        }

        $hasActiveReservation = $listing->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveReservation) {
            throw ValidationException::withMessages([
                'listing' => [
                    'This listing already has an active reservation.',
                ],
            ]);
        }

        return DB::transaction(function () use ($user, $listing) {
            return Reservation::create([
                'listing_id' => $listing->id,
                'user_id' => $user->id,
                'offer_id' => null,
                'status' => 'pending',
                'reserved_at' => now(),
                'expires_at' => now()->addDay(),
            ]);
        });
    }
}
