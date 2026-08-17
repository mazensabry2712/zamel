<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\CreateListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Resources\ListingResource;
use App\Support\ApiResponse;

class ListingController extends Controller
{
    public function store(
        StoreListingRequest $request,
        CreateListing $createListing
    ) {
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
}
