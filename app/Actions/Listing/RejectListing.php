<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class RejectListing
{
    public function execute(
        Listing $listing,
        string $reason
    ): Listing {
        if ($listing->moderation_status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => [
                    'Only pending listings can be rejected.',
                ],
            ]);
        }

        $listing->update([
            'moderation_status' => 'rejected',
            'moderation_reason' => $reason,
            'moderated_at' => now(),
        ]);

        return $listing->refresh();
    }
}
