<?php

namespace App\Actions\Offer;

use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;

class AcceptOffer
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

        if ($offer->status !== 'pending') {
            abort(422, 'Only pending offers can be accepted.');
        }

        if ($request->status !== 'open') {
            abort(422, 'Only open requests can accept offers.');
        }

        if ($request->expires_at && $request->expires_at->isPast()) {
            abort(422, 'This request has expired.');
        }

        if ($offer->expires_at && $offer->expires_at->isPast()) {
            abort(422, 'This offer has expired.');
        }

        $offer->update([
            'status' => 'accepted',
        ]);

        return $offer->refresh();
    }
}
