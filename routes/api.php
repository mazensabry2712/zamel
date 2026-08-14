<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/


Route::prefix('v1')->group(function () {

    Route::middleware('throttle:api')->group(function () {

        Route::get('/health', function () {
            return response()->json([
                'success' => true,
                'message' => 'API is running',
                'version' => 'v1',
            ]);
        });
    });
});
