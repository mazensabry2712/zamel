<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class MarkListingAsSold
{
    public function execute(Listing $listing): Listing
    {
        if ($listing->status !== 'published') {
            throw ValidationException::withMessages([
                'listing' => [
                    'Only published listings can be marked as sold.',
                ],
            ]);
        }

        $listing->update([
            'status' => 'sold',
        ]);

        return $listing->refresh();
    }
}
