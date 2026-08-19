<?php

namespace App\Actions\Reservation;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompleteReservation
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

        if ($reservation->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'reservation' => [
                    'Only confirmed reservations can be completed.',
                ],
            ]);
        }

        $reservation->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $reservation->refresh();
    }
}
