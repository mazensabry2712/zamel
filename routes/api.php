<?php

use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\Admin\CategoryModerationController;
use App\Http\Controllers\Api\V1\Admin\UserModerationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ListingController;
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

    Route::middleware([
        'auth:sanctum',
        'active',
    ])->group(function () {
        Route::post('/categories', [
            CategoryController::class,
            'store',
        ]);

        Route::post('/listings', [
            ListingController::class,
            'store',
        ]);
    });

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

            Route::prefix('categories/{category}')->group(function () {
                Route::put('/approve', [
                    CategoryModerationController::class,
                    'approve',
                ]);

                Route::put('/reject', [
                    CategoryModerationController::class,
                    'reject',
                ]);
            });
        });

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

    Route::get('/categories', [
        CategoryController::class,
        'index',
    ]);

    Route::get('/categories/{category}', [
        CategoryController::class,
        'show',
    ]);

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => 'v1',
        ]);
    });
});
