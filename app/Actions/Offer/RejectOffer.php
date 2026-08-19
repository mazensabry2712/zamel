<?php

namespace App\Actions\Offer;

use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;

class RejectOffer
{
    public function execute(
        MarketplaceRequest $request,
        Offer $offer,
        User $user
    ): Offer {
        if ($offer->request_id !== $request->id) {
            abort(422, 'Offer does not belong to this request.');
        }

        if ($request->user_id !== $user->id) {
            abort(403);
        }

        if ($offer->user_id === $request->user_id) {
            abort(403, 'The request owner cannot reject their own offer.');
        }

        if ($offer->status !== 'pending') {
            abort(422, 'Only pending offers can be rejected.');
        }

        if ($request->status !== 'open') {
            abort(422, 'Only offers on open requests can be rejected.');
        }

        if ($request->expires_at && $request->expires_at->isPast()) {
            abort(422, 'This request has expired.');
        }

        if ($offer->expires_at && $offer->expires_at->isPast()) {
            abort(422, 'This offer has expired.');
        }

        $offer->update([
            'status' => 'rejected',
        ]);

        return $offer->refresh();
    }
}
