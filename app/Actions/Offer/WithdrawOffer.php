<?php

namespace App\Actions\Offer;

use App\Models\MarketplaceRequest;
use App\Models\Offer;
use App\Models\User;

class WithdrawOffer
{
    public function execute(
        MarketplaceRequest $request,
        Offer $offer,
        User $user
    ): Offer {
        if ($offer->request_id !== $request->id) {
            abort(422, 'Offer does not belong to this request.');
        }

        if ($offer->user_id !== $user->id) {
            abort(403);
        }

        if ($offer->status !== 'pending') {
            abort(422, 'Only pending offers can be withdrawn.');
        }

        if ($request->status !== 'open') {
            abort(422, 'Only offers on open requests can be withdrawn.');
        }

        if ($offer->expires_at && $offer->expires_at->isPast()) {
            abort(422, 'This offer has expired.');
        }

        $offer->update([
            'status' => 'withdrawn',
        ]);

        return $offer->refresh();
    }
}
