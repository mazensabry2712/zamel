<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Category\CreateCategorySuggestion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\SuggestCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            data: CategoryResource::collection($categories),
            message: 'Categories retrieved successfully.',
        );
    }

    public function show(Category $category)
    {
        if (
            $category->status !== 'approved' ||
            ! $category->is_active
        ) {
            abort(404);
        }

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category retrieved successfully.',
        );
    }

    public function store(
        SuggestCategoryRequest $request,
        CreateCategorySuggestion $createCategorySuggestion
    ) {
        $category = $createCategorySuggestion->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category submitted for review successfully.',
            status: 201,
        );
    }
}
