<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ChangeUserPassword;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Actions\Profile\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        RegisterUser $registerUser
    ) {
        $user = $registerUser->execute(
            $request->validated()
        );

        $user->load('profile');

        $token = $user->createToken(
            'api-token'
        )->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
                'profile' => new ProfileResource($user->profile),
                'token' => $token,
            ],
            message: 'Registration successful.',
            status: 201
        );
    }

    public function login(
        LoginRequest $request,
        LoginUser $loginUser
    ) {
        $request->ensureIsNotRateLimited();

        try {
            $user = $loginUser->execute(
                $request->string('email')->toString(),
                $request->string('password')->toString()
            );

            $request->clearRateLimiter();
        } catch (ValidationException $exception) {
            $request->hitRateLimiter();

            throw $exception;
        }

        $user->load('profile');

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
                'profile' => $user->profile
                    ? new ProfileResource($user->profile)
                    : null,
                'token' => $token,
            ],
            message: 'Login successful.'
        );
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('profile');

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
            ],
            message: 'User retrieved successfully.'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(
            message: 'Logout successful.'
        );
    }

    public function profile(Request $request)
    {
        $profile = $request->user()
            ->profile()
            ->with([
                'university',
                'faculty',
                'school',
                'academicYear',
            ])
            ->first();

        return ApiResponse::success(
            data: [
                'profile' => $profile
                    ? new ProfileResource($profile)
                    : null,
            ],
            message: 'Profile retrieved successfully.'
        );
    }

    public function updateProfile(
        UpdateProfileRequest $request,
        UpdateProfile $updateProfile
    ) {
        $profile = $request->user()->profile;

        $profile = $updateProfile->execute(
            $profile,
            $request->validated()
        );

        $profile->load([
            'university',
            'faculty',
            'school',
            'academicYear',
        ]);

        return ApiResponse::success(
            data: [
                'profile' => new ProfileResource($profile),
            ],
            message: 'Profile updated successfully.'
        );
    }

    public function changePassword(
        ChangePasswordRequest $request,
        ChangeUserPassword $changeUserPassword
    ) {
        $changeUserPassword->execute(
            $request->user(),
            $request->string('password')->toString()
        );

        return ApiResponse::success(
            message: 'Password changed successfully.'
        );
    }
}
