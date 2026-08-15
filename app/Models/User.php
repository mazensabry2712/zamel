<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Resources\ProfileResource;
use App\Support\ApiResponse;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
