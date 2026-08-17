<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class ApproveListing
{
    public function execute(Listing $listing): Listing
    {
        if ($listing->moderation_status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => [
                    'Only pending listings can be approved.',
                ],
            ]);
        }

        $listing->update([
            'moderation_status' => 'approved',
            'moderation_reason' => null,
            'moderated_at' => now(),
        ]);

        return $listing->refresh();
    }
}
