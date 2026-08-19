<?php

namespace App\Actions\MarketplaceRequest;

use App\Models\MarketplaceRequest;
use App\Models\User;

class CancelMarketplaceRequest
{
    public function execute(
        MarketplaceRequest $marketplaceRequest,
        User $user,
    ): MarketplaceRequest {
        if ($marketplaceRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($marketplaceRequest->status !== 'open') {
            abort(422, 'Only open requests can be cancelled.');
        }

        if ($marketplaceRequest->expires_at && $marketplaceRequest->expires_at->isPast()) {
            abort(422, 'This request has expired.');
        }

        $marketplaceRequest->update([
            'status' => 'cancelled',
        ]);

        return $marketplaceRequest->refresh();
    }
}
