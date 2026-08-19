<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Offer\CreateOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\MarketplaceRequest;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;

class OfferController extends Controller
{
    public function store(
        StoreOfferRequest $request,
        MarketplaceRequest $marketplaceRequest,
        CreateOffer $createOffer,
    ): JsonResponse {
        $offer = $createOffer->execute(
            user: $request->user(),
            marketplaceRequest: $marketplaceRequest,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new OfferResource($offer),
            message: 'Offer created successfully.',
            status: 201,
        );
    }
}
