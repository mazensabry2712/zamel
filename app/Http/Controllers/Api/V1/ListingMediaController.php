<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\AddListingImage;
use App\Actions\Listing\DeleteListingImage;
use App\Actions\Listing\ReorderListingImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\ReorderListingImagesRequest;
use App\Http\Requests\Listing\StoreListingImageRequest;
use App\Http\Resources\MediaResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ListingMediaController extends Controller
{
    public function store(
        StoreListingImageRequest $request,
        Listing $listing,
        AddListingImage $addListingImage,
    ): MediaResource {
        Gate::authorize('update', $listing);

        $media = $addListingImage->execute(
            listing: $listing,
            image: $request->file('image'),
        );

        return new MediaResource($media);
    }

    public function destroy(
        Listing $listing,
        Media $media,
        DeleteListingImage $deleteListingImage,
    ): JsonResponse {
        Gate::authorize('update', $listing);

        $deleteListingImage->execute(
            listing: $listing,
            media: $media,
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing image deleted successfully.',
        ]);
    }

    public function reorder(
        ReorderListingImagesRequest $request,
        Listing $listing,
        ReorderListingImages $reorderListingImages,
    ): JsonResponse {
        Gate::authorize('update', $listing);

        $media = $reorderListingImages->execute(
            listing: $listing,
            mediaIds: $request->validated('media_ids'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing images reordered successfully.',
            'data' => MediaResource::collection($media),
        ]);
    }
}
