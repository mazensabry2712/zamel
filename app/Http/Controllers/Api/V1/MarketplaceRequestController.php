<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\MarketplaceRequest\CreateMarketplaceRequest;
use App\Actions\MarketplaceRequest\DeleteMarketplaceRequest;
use App\Actions\MarketplaceRequest\UpdateMarketplaceRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketplaceRequest;
use App\Http\Requests\UpdateMarketplaceRequest as UpdateMarketplaceRequestForm;
use App\Http\Resources\MarketplaceRequestResource;
use App\Models\MarketplaceRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MarketplaceRequestController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $requests = MarketplaceRequest::query()
            ->with(['category', 'user'])
            ->where('status', 'open')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return MarketplaceRequestResource::collection($requests);
    }

    public function store(
        StoreMarketplaceRequest $request,
        CreateMarketplaceRequest $createMarketplaceRequest
    ): JsonResponse {
        $marketplaceRequest = $createMarketplaceRequest->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new MarketplaceRequestResource($marketplaceRequest->load(['category', 'user'])),
            message: 'Request created successfully.',
            status: 201,
        );
    }

    public function show(MarketplaceRequest $marketplaceRequest): MarketplaceRequestResource
    {
        Gate::authorize('view', $marketplaceRequest);

        return new MarketplaceRequestResource(
            $marketplaceRequest->load(['category', 'user'])
        );
    }

    public function update(
        UpdateMarketplaceRequestForm $request,
        MarketplaceRequest $marketplaceRequest,
        UpdateMarketplaceRequest $updateMarketplaceRequest
    ): MarketplaceRequestResource {
        Gate::authorize('update', $marketplaceRequest);

        $marketplaceRequest = $updateMarketplaceRequest->execute(
            marketplaceRequest: $marketplaceRequest,
            data: $request->validated(),
        );

        return new MarketplaceRequestResource(
            $marketplaceRequest->load(['category', 'user'])
        );
    }

    public function destroy(
        MarketplaceRequest $marketplaceRequest,
        DeleteMarketplaceRequest $deleteMarketplaceRequest
    ): JsonResponse {
        Gate::authorize('delete', $marketplaceRequest);

        $deleteMarketplaceRequest->execute($marketplaceRequest);

        return ApiResponse::success(
            data: null,
            message: 'Request deleted successfully.',
        );
    }
}
