<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacultyResource;
use App\Http\Resources\UniversityResource;
use App\Models\University;
use App\Support\ApiResponse;

class UniversityController extends Controller
{
    public function index()
    {
        $universities = University::query()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            data: UniversityResource::collection($universities),
            message: 'Universities retrieved successfully.'
        );
    }

    public function show(University $university)
    {
        return ApiResponse::success(
            data: new UniversityResource($university),
            message: 'University retrieved successfully.'
        );
    }
<<<<<<< HEAD
=======

    public function faculties(University $university)
    {
        $faculties = $university->faculties()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            data: FacultyResource::collection($faculties),
            message: 'University faculties retrieved successfully.'
        );
    }
>>>>>>> 9b584c399be498740e4073dad7543e71aafd55f6
}
