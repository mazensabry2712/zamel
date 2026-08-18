<?php

namespace App\Policies;

use App\Models\MarketplaceRequest;
use App\Models\User;

class MarketplaceRequestPolicy
{
    public function view(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            || ($marketplaceRequest->status === 'open'
                && ($marketplaceRequest->expires_at === null || $marketplaceRequest->expires_at->isFuture()));
    }

    public function update(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            && $marketplaceRequest->status === 'open'
            && ($marketplaceRequest->expires_at === null || $marketplaceRequest->expires_at->isFuture());
    }

    public function delete(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            && $marketplaceRequest->status === 'open'
            && ($marketplaceRequest->expires_at === null || $marketplaceRequest->expires_at->isFuture());
    }
}
