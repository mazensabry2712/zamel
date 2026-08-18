<?php

namespace App\Policies;

use App\Models\MarketplaceRequest;
use App\Models\User;

class MarketplaceRequestPolicy
{
    public function view(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            || $marketplaceRequest->status === 'open';
    }

    public function update(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            && $marketplaceRequest->status === 'open';
    }

    public function delete(User $user, MarketplaceRequest $marketplaceRequest): bool
    {
        return $marketplaceRequest->user_id === $user->id
            && $marketplaceRequest->status === 'open';
    }
}
