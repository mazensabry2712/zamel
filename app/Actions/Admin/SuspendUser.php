<?php

namespace App\Actions\Admin;

use App\Models\User;
use Carbon\Carbon;

class SuspendUser
{
    public function execute(
        User $user,
        Carbon $until,
        string $reason
    ): User {
        $user->update([
            'status' => 'suspended',
            'suspended_until' => $until,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
        ]);

        $user->tokens()->delete();

        return $user->refresh();
    }
}
