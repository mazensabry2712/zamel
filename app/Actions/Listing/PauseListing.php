<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class PauseListing
{
    public function execute(Listing $listing): Listing
    {
        if ($listing->status !== 'published') {
            throw ValidationException::withMessages([
                'listing' => [
                    'Only published listings can be paused.',
                ],
            ]);
        }

        $listing->update([
            'status' => 'paused',
        ]);

        return $listing->refresh();
    }
}
