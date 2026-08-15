<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/


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



        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', [
                AuthController::class,
                'me',
            ]);

            Route::post('/logout', [
                AuthController::class,
                'logout',
            ]);
        });
        Route::get('/profile', [
            AuthController::class,
            'profile',
        ]);
    });


    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => 'v1',
        ]);
    });
});
