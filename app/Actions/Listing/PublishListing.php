<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class PublishListing
{
    public function execute(Listing $listing): Listing
    {
        if ($listing->moderation_status !== 'approved') {
            throw ValidationException::withMessages([
                'listing' => [
                    'The listing must be approved before it can be published.',
                ],
            ]);
        }

        if ($listing->status === 'published') {
            throw ValidationException::withMessages([
                'listing' => [
                    'The listing is already published.',
                ],
            ]);
        }

        $listing->update([
            'status' => 'published',
            'published_at' => $listing->published_at ?? now(),
        ]);

        return $listing->refresh();
    }
}
