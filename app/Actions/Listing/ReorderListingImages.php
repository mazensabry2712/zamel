<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReorderListingImages
{
    public function execute(Listing $listing, array $mediaIds): array
    {
        $media = $listing->getMedia('images');
        $existingIds = $media->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $requestedIds = collect($mediaIds)->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($existingIds !== $requestedIds) {
            throw ValidationException::withMessages([
                'media_ids' => [
                    'The media_ids must contain exactly the images belonging to this listing.',
                ],
            ]);
        }

        foreach ($mediaIds as $position => $mediaId) {
            /** @var Media $mediaItem */
            $mediaItem = $media->firstWhere('id', (int) $mediaId);
            $mediaItem->order_column = $position;
            $mediaItem->save();
        }

        $listing->resetForModerationReview();
        $listing->save();

        return $listing->getMedia('images')
            ->sortBy('order_column')
            ->values()
            ->all();
    }
}
