<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\CreateListing;
use App\Actions\Listing\DeleteListing;
use App\Actions\Listing\MarkListingAsSold;
use App\Actions\Listing\PauseListing;
use App\Actions\Listing\PublishListing;
use App\Actions\Listing\UpdateListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('update', $listing);

        $listing = $updateListing->execute(
            listing: $listing,
            data: $request->validated(),
        );

        return new ListingResource($listing->load('category'));
    }

    public function destroy(
        Listing $listing,
        DeleteListing $deleteListing
    ): JsonResponse {
        Gate::authorize('delete', $listing);

        $deleteListing->execute($listing);

        return ApiResponse::success(
            data: null,
            message: 'Listing deleted successfully.',
        );
    }

    public function publish(
        Listing $listing,
        PublishListing $publishListing
    ): ListingResource {
        Gate::authorize('publish', $listing);

        $listing = $publishListing->execute($listing);

        return new ListingResource($listing->load('category'));
    }

    public function pause(
        Listing $listing,
        PauseListing $pauseListing
    ): ListingResource {
        Gate::authorize('pause', $listing);

        $listing = $pauseListing->execute($listing);

        return new ListingResource($listing->load('category'));
    }

    public function sold(
        Listing $listing,
        MarkListingAsSold $markListingAsSold
    ): ListingResource {
        Gate::authorize('markAsSold', $listing);

        $listing = $markListingAsSold->execute($listing);

        return new ListingResource($listing->load('category'));
    }
}
