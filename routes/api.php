<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\CategoryController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('jwt.auth')->group(function () {

    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 🔥 DEBUG ROUTE (correct)
    Route::get('/debug-admin', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });

    // 🔥 ADMIN ROUTES (correct placement)
    Route::middleware('role:admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    Route::apiResource('categories', CategoryController::class);

});