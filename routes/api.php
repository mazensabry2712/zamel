<?php

use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\Admin\CategoryModerationController;
use App\Http\Controllers\Api\V1\Admin\ListingModerationController;
use App\Http\Controllers\Api\V1\Admin\UserModerationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\ListingMediaController;
use App\Http\Controllers\Api\V1\MarketplaceRequestController;
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

        Route::put('/listings/{listing}', [
            ListingController::class,
            'update',
        ]);

        Route::delete('/listings/{listing}', [
            ListingController::class,
            'destroy',
        ]);

        Route::post('/listings/{listing}/publish', [
            ListingController::class,
            'publish',
        ]);

        Route::post('/listings/{listing}/pause', [
            ListingController::class,
            'pause',
        ]);

        Route::post('/listings/{listing}/sold', [
            ListingController::class,
            'sold',
        ]);

        Route::post('/listings/{listing}/images', [
            ListingMediaController::class,
            'store',
        ]);

        Route::delete('/listings/{listing}/images/{media}', [
            ListingMediaController::class,
            'destroy',
        ]);

        Route::put('/listings/{listing}/images/reorder', [
            ListingMediaController::class,
            'reorder',
        ]);

        Route::get('/favorites', [
            FavoriteController::class,
            'index',
        ]);

        Route::post('/listings/{listing}/favorite', [
            FavoriteController::class,
            'store',
        ]);

        Route::delete('/listings/{listing}/favorite', [
            FavoriteController::class,
            'destroy',
        ]);

        Route::post('/requests', [
            MarketplaceRequestController::class,
            'store',
        ]);

        Route::get('/requests', [
            MarketplaceRequestController::class,
            'index',
        ]);

        Route::get('/requests/{marketplaceRequest}', [
            MarketplaceRequestController::class,
            'show',
        ]);

        Route::put('/requests/{marketplaceRequest}', [
            MarketplaceRequestController::class,
            'update',
        ]);

        Route::delete('/requests/{marketplaceRequest}', [
            MarketplaceRequestController::class,
            'destroy',
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

            Route::prefix('listings/{listing}')->group(function () {
                Route::put('/approve', [
                    ListingModerationController::class,
                    'approve',
                ]);

                Route::put('/reject', [
                    ListingModerationController::class,
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

    Route::get('/listings/{listing}', [
        ListingController::class,
        'show',
    ]);

    Route::get('/listings', [
        ListingController::class,
        'index',
    ]);

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => 'v1',
        ]);
    });
});
