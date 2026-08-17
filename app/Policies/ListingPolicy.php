<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function publish(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function pause(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function markAsSold(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }
}
