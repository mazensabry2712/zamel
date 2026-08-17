<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\BanUser;
use App\Actions\Admin\SuspendUser;
use App\Actions\Admin\UnbanUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BanUserRequest;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Carbon\Carbon;

class UserModerationController extends Controller
{
    public function suspend(
        SuspendUserRequest $request,
        User $user,
        SuspendUser $suspendUser
    ) {
        $user = $suspendUser->execute(
            user: $user,
            until: Carbon::parse(
                $request->validated('suspended_until')
            ),
            reason: $request->validated('reason'),
        );

        return ApiResponse::success(
            data: [
                'user_id' => $user->id,
                'status' => $user->status,
                'suspended_until' => $user->suspended_until,
                'reason' => $user->moderation_reason,
            ],
            message: 'User suspended successfully.',
        );
    }

    public function ban(
        BanUserRequest $request,
        User $user,
        BanUser $banUser
    ) {
        $user = $banUser->execute(
            user: $user,
            reason: $request->validated('reason'),
        );

        return ApiResponse::success(
            data: [
                'user_id' => $user->id,
                'status' => $user->status,
                'reason' => $user->moderation_reason,
            ],
            message: 'User banned successfully.',
        );
    }

    public function unban(
        User $user,
        UnbanUser $unbanUser
    ) {
        $user = $unbanUser->execute($user);

        return ApiResponse::success(
            data: [
                'user_id' => $user->id,
                'status' => $user->status,
            ],
            message: 'User unbanned successfully.',
        );
    }
}
