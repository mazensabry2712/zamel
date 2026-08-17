<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Listing\ApproveListing;
use App\Actions\Listing\RejectListing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Support\ApiResponse;

class ListingModerationController extends Controller
{
    public function approve(
        Listing $listing,
        ApproveListing $approveListing
    ) {
        $listing = $approveListing->execute($listing);

        return ApiResponse::success(
            data: new ListingResource($listing),
            message: 'Listing approved successfully.',
        );
    }

    public function reject(
        RejectListingRequest $request,
        Listing $listing,
        RejectListing $rejectListing
    ) {
        $listing = $rejectListing->execute(
            listing: $listing,
            reason: $request->validated('reason'),
        );

        return ApiResponse::success(
            data: new ListingResource($listing),
            message: 'Listing rejected successfully.',
        );
    }
}
