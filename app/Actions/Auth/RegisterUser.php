<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'university_id' => $data['university_id'],
            'faculty_id' => $data['faculty_id'],
            'academic_year_id' => $data['academic_year_id'],



        ]);

       
    }
}
