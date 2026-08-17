<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\CreateListing;
use App\Actions\Listing\UpdateListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ListingController extends Controller
{
    public function store(
        StoreListingRequest $request,
        CreateListing $createListing
    ): JsonResponse {
        $listing = $createListing->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new ListingResource($listing),
            message: 'Listing created successfully.',
            status: 201,
        );
    }

    public function show(Listing $listing): ListingResource
    {
        $listing = Listing::query()
            ->with('category')
            ->whereKey($listing->getKey())
            ->where('status', 'published')
            ->where('moderation_status', 'approved')
            ->whereHas('category', function ($query) {
                $query
                    ->where('status', 'approved')
                    ->where('is_active', true);
            })
            ->firstOrFail();

        return new ListingResource($listing);
    }

    public function update(
        UpdateListingRequest $request,
        Listing $listing,
        UpdateListing $updateListing
    ): ListingResource {
        $this->authorize('update', $listing);

        $listing = $updateListing->execute(
            listing: $listing,
            data: $request->validated(),
        );

        return new ListingResource($listing->load('category'));
    }
}
