<?php

namespace App\Actions\Listing;

use App\Models\Listing;

class DeleteListing
{
    public function execute(Listing $listing): void
    {
        $listing->delete();
    }
}
