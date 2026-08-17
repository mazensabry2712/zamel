<?php

namespace App\Actions\Admin;

use App\Models\User;

class UnbanUser
{
    public function execute(User $user): User
    {
        $user->update([
            'status' => 'active',
            'suspended_until' => null,
            'moderation_reason' => null,
            'moderated_at' => now(),
        ]);

        return $user->refresh();
    }
}
