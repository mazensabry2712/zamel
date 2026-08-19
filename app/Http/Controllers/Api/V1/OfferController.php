<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Offer\AcceptOffer;
use App\Actions\Offer\CreateOffer;
use App\Actions\Offer\RejectOffer;
use App\Actions\Offer\WithdrawOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\MarketplaceRequest;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OfferController extends Controller
{
    public function index(Request $request, MarketplaceRequest $marketplaceRequest): AnonymousResourceCollection
    {
        Gate::authorize('viewOffers', $marketplaceRequest);

        $offers = $marketplaceRequest->offers()
            ->with('user')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        return OfferResource::collection($offers);
    }

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

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully.',
            'data' => new OfferResource($offer),
        ], 201);
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

    public function reject(
        MarketplaceRequest $marketplaceRequest,
        Offer $offer,
        RejectOffer $rejectOffer
    ): OfferResource {
        $offer = $rejectOffer->execute(
            request: $marketplaceRequest,
            offer: $offer,
            user: request()->user(),
        );

        return new OfferResource(
            $offer->load(['user', 'marketplaceRequest'])
        );
    }
}
