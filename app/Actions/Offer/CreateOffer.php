<?php

namespace App\Actions\Offer;

use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateOffer
{
    public function execute(
        User $user,
        MarketplaceRequest $marketplaceRequest,
        array $data,
    ): Offer {
        if ($marketplaceRequest->user_id === $user->id) {
            throw ValidationException::withMessages([
                'request' => [
                    'You cannot make an offer on your own request.',
                ],
            ]);
        }

        if ($marketplaceRequest->status !== 'open') {
            throw ValidationException::withMessages([
                'request' => [
                    'The selected request is not open for offers.',
                ],
            ]);
        }

        if ($marketplaceRequest->expires_at !== null && $marketplaceRequest->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'request' => [
                    'The selected request has expired.',
                ],
            ]);
        }

        return Offer::create([
            'request_id' => $marketplaceRequest->id,
            'user_id' => $user->id,
            'price' => $data['price'],
            'condition' => $data['condition'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }
}
