<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\MarkListingAsSold;
use App\Actions\Reservation\CancelReservation;
use App\Actions\Reservation\CompleteReservation;
use App\Actions\Reservation\ConfirmReservation;
use App\Actions\Reservation\CreateReservation;
use App\Actions\Transaction\CompleteTransaction;
use App\Actions\Transaction\CreateTransaction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Listing;
use App\Models\Reservation;
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

    public function confirm(
        Listing $listing,
        Reservation $reservation,
        ConfirmReservation $confirmReservation,
        CreateTransaction $createTransaction,
    ): ReservationResource {
        $reservation = $confirmReservation->execute(
            user: request()->user(),
            listing: $listing,
            reservation: $reservation,
            createTransaction: $createTransaction,
        );

        return new ReservationResource($reservation);
    }

    public function cancel(
        Listing $listing,
        Reservation $reservation,
        CancelReservation $cancelReservation,
    ): ReservationResource {
        $reservation = $cancelReservation->execute(
            user: request()->user(),
            listing: $listing,
            reservation: $reservation,
        );

        return new ReservationResource($reservation);
    }

    public function complete(
        Listing $listing,
        Reservation $reservation,
        CompleteReservation $completeReservation,
        MarkListingAsSold $markListingAsSold,
        CompleteTransaction $completeTransaction,
    ): ReservationResource {
        $reservation = $completeReservation->execute(
            user: request()->user(),
            listing: $listing,
            reservation: $reservation,
            markListingAsSold: $markListingAsSold,
            completeTransaction: $completeTransaction,
        );

        return new ReservationResource($reservation);
    }
}