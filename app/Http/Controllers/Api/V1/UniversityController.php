<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
}
