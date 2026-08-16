<?php

use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\UniversityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [
            AuthController::class,
            'register',
        ]);

        Route::post('/login', [
            AuthController::class,
            'login',
        ]);

        // Route::middleware('auth:sanctum')->group(function () {
        Route::middleware([
            'auth:sanctum',
            'active',
        ])->group(function () {

            Route::get('/me', [
                AuthController::class,
                'me',
            ]);

            Route::post('/logout', [
                AuthController::class,
                'logout',
            ]);

            Route::get('/profile', [
                AuthController::class,
                'profile',
            ]);

            Route::put('/profile', [
                AuthController::class,
                'updateProfile',
            ]);

            Route::put('/change-password', [
                AuthController::class,
                'changePassword',
            ]);
        });
    });
    // University Context
    Route::get('/universities', [
        UniversityController::class,
        'index',
    ]);

    Route::get('/universities/{university}', [
        UniversityController::class,
        'show',
    ]);

    Route::get('/universities/{university}/faculties', [
        UniversityController::class,
        'faculties',
    ]);

    Route::get('/academic-years', [
        AcademicYearController::class,
        'index',
    ]);

    // Category
    Route::get('/categories', [
        CategoryController::class,
        'index',
    ]);

    Route::get('/categories/{category}', [
        CategoryController::class,
        'show',
    ]);

    // Test Api Health
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => 'v1',
        ]);
    });
});
