<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Offer\AcceptOffer;
use App\Actions\Offer\CreateOffer;
use App\Actions\Offer\WithdrawOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

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

    public function accept(
        MarketplaceRequest $marketplaceRequest,
        Offer $offer,
        AcceptOffer $acceptOffer
    ): OfferResource {
        $offer = $acceptOffer->execute(
            request: $marketplaceRequest,
            offer: $offer,
            user: request()->user(),
        );

        return new OfferResource(
            $offer->load(['user', 'marketplaceRequest'])
        );
    }

    public function withdraw(
        MarketplaceRequest $marketplaceRequest,
        Offer $offer,
        WithdrawOffer $withdrawOffer
    ): OfferResource {
        $offer = $withdrawOffer->execute(
            request: $marketplaceRequest,
            offer: $offer,
            user: request()->user(),
        );

        return new OfferResource(
            $offer->load(['user', 'marketplaceRequest'])
        );
    }
}
