<?php

namespace App\Actions\Auth;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->profile()->create([
                'education_type' => $data['education_type'],
                'university_id' => $data['university_id'] ?? null,
                'faculty_id' => $data['faculty_id'] ?? null,
                'school_id' => $data['school_id'] ?? null,
                'academic_year_id' => $data['academic_year_id'] ?? null,
            ]);

            return $user;
        });
    }
}
