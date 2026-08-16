<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;

class CategoryController extends Controller
{
    public function index()
    {
        $cartegories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            data: CategoryResource::collection($cartegories),
            message: 'Categories retrieved successfully.',
        );
    }

    public function show(Category $category)
    {

        if (! $category->is_active) {
            abort(404);
        }

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category retrieved successfully.',
        );
    }
}
