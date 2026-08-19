<?php

namespace App\Actions\Reservation;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CancelReservation
{
    public function execute(
        User $user,
        Listing $listing,
        Reservation $reservation,
    ): Reservation {
        if ($reservation->listing_id !== $listing->id) {
            abort(404);
        }

        if ($reservation->user_id !== $user->id && $listing->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($reservation->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'reservation' => [
                    'Only pending or confirmed reservations can be cancelled.',
                ],
            ]);
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $reservation->refresh();
    }
}
