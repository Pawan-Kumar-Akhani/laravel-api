<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (JWT Auth)
|--------------------------------------------------------------------------
*/

Route::middleware('jwt.auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'getProfile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });


    /*
    |--------------------------------------------------------------------------
    | Products (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('products', ProductController::class);


    /*
    |--------------------------------------------------------------------------
    | Categories (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('categories', CategoryController::class);


    /*
    |--------------------------------------------------------------------------
    | Debug (optional - remove in production)
    |--------------------------------------------------------------------------
    */
    Route::get('/debug-user', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });

});