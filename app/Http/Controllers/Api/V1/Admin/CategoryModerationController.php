<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Category\ApproveCategory;
use App\Actions\Category\RejectCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;

class CategoryModerationController extends Controller
{
    public function approve(
        Category $category,
        ApproveCategory $approveCategory
    ) {
        $category = $approveCategory->execute($category);

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category approved successfully.',
        );
    }

    public function reject(
        RejectCategoryRequest $request,
        Category $category,
        RejectCategory $rejectCategory
    ) {
        $category = $rejectCategory->execute(
            category: $category,
            reason: $request->validated('reason'),
        );

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category rejected successfully.',
        );
    }
}
