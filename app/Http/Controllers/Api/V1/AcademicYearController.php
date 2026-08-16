<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicYear::query();

        if ($request->filled('education_type')) {
            $query->where(
                'education_type',
                $request->string('education_type')->toString()
            );
        }

        $academicYears = $query
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success(
            data: AcademicYearResource::collection($academicYears),
            message: 'Academic years retrieved successfully.'
        );
    }
}
