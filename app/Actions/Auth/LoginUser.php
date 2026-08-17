<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(
        string $email,
        string $password
    ): User {
        $user = User::where('email', $email)->first();

        if (
            ! $user ||
            ! Hash::check($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        if ($user->status === 'banned') {
            throw ValidationException::withMessages([
                'email' => [
                    'Your account has been banned.',
                ],
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'email' => [
                    'Your account is temporarily suspended.',
                ],
            ]);
        }

        return $user;
    }
}
