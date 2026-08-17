<?php

use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\UniversityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\UserModerationController;

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



// Admin

Route::prefix('admin')
    ->middleware([
        'auth:sanctum',
        'active',
        'can:admin',
    ])
    ->group(function () {

        Route::prefix('users/{user}')->group(function () {

    Route::put('/suspend', [
        UserModerationController::class,
        'suspend',
    ]);

    Route::put('/ban', [
        UserModerationController::class,
        'ban',
    ]);

    Route::put('/unban', [
        UserModerationController::class,
        'unban',
    ]);
});
    });

/////////////













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
