<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Authorization\AuthorizationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteListingImage
{
    public function execute(Listing $listing, Media $media): void
    {
        $this->ensureBelongsToListing($listing, $media);

        $media->delete();
    }

    private function ensureBelongsToListing(Listing $listing, Media $media): void
    {
        if (
            $media->model_type !== $listing->getMorphClass()
            || (int) $media->model_id !== (int) $listing->getKey()
            || $media->collection_name !== 'images'
        ) {
            throw new AuthorizationException('The image does not belong to this listing.');
        }
    }
}
