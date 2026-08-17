<?php

namespace App\Actions\Admin;

use App\Models\User;

class BanUser
{
    public function execute(
        User $user,
        string $reason
    ): User {
        $user->update([
            'status' => 'banned',
            'suspended_until' => null,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
        ]);

        return $user->refresh();
    }
}
