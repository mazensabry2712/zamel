<?php

namespace App\Actions\Reservation;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConfirmReservation
{
    public function execute(
        User $user,
        Listing $listing,
        Reservation $reservation,
    ): Reservation {
        if ($reservation->listing_id !== $listing->id) {
            abort(404);
        }

        if ($listing->user_id !== $user->id) {
            abort(403);
        }

        if ($reservation->status !== 'pending') {
            throw ValidationException::withMessages([
                'reservation' => [
                    'Only pending reservations can be confirmed.',
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

        $reservation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return $reservation->refresh();
    }
}
