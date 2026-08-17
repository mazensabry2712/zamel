<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AddListingImage
{
    private const MAX_IMAGES = 8;

    public function execute(Listing $listing, UploadedFile $image): Media
    {
        if ($listing->getMedia('images')->count() >= self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'image' => [
                    'A listing can contain a maximum of 8 images.',
                ],
            ]);
        }

        return $listing
            ->addMedia($image)
            ->toMediaCollection('images');
    }
}
