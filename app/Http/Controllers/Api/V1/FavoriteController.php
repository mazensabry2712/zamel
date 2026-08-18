<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Models\Listing;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['listing.category'])
            ->whereHas('listing', function ($query) {
                $query
                    ->where('status', 'published')
                    ->where('moderation_status', 'approved')
                    ->whereHas('category', function ($query) {
                        $query
                            ->where('status', 'approved')
                            ->where('is_active', true);
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return FavoriteResource::collection($favorites);
    }

    public function store(Request $request, Listing $listing): JsonResponse
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

        $user = $request->user();

        if ($user->favorites()->where('listing_id', $listing->getKey())->exists()) {
            return ApiResponse::error(
                message: 'Listing is already in favorites.',
                status: 409,
            );
        }

        $favorite = $user->favorites()->create([
            'listing_id' => $listing->getKey(),
        ]);

        return ApiResponse::success(
            data: new FavoriteResource($favorite->load(['listing.category'])),
            message: 'Listing added to favorites.',
            status: 201,
        );
    }

    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        $favorite = $request->user()
            ->favorites()
            ->where('listing_id', $listing->getKey())
            ->firstOrFail();

        $favorite->delete();

        return ApiResponse::success(
            data: null,
            message: 'Listing removed from favorites.',
        );
    }
}
