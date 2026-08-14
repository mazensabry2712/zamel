<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
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

        $token = $user->createToken(
            'api-token'
        )->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
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

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            message: 'Login successful.'
        );
    }

    public function me()
{
    return ApiResponse::success(
        data: [
            'user' => new UserResource(
                request()->user()
            ),
        ],
        message: 'User retrieved successfully.'
    );
}
}
