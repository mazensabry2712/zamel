<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Reservation\CreateReservation;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function store(
        Listing $listing,
        CreateReservation $createReservation,
    ): JsonResponse {
        $reservation = $createReservation->execute(
            user: request()->user(),
            listing: $listing,
        );

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully.',
            'data' => new ReservationResource($reservation),
        ], 201);
    }
}
