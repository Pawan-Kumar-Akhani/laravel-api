<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\DashboardController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
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
| PROTECTED ROUTES (JWT AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware('jwt.auth')->group(function () {

    /*
    |----------------------------------------
    | USER PROFILE
    |----------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'getProfile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /*
    |----------------------------------------
    | PRODUCTS (CRUD)
    |----------------------------------------
    */
    Route::apiResource('products', ProductController::class);

    /*
    |----------------------------------------
    | CATEGORIES (CRUD)
    |----------------------------------------
    */
    Route::apiResource('categories', CategoryController::class);

    /*
    |----------------------------------------
    | CART SYSTEM
    |----------------------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::get('/', [CartController::class, 'getCart']);
        Route::put('/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/{id}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
    });

    /*
    |----------------------------------------
    | ORDER SYSTEM
    |----------------------------------------
    */
    Route::prefix('orders')->group(function () {
        Route::post('/place', [OrderController::class, 'placeOrder']);
        Route::get('/', [OrderController::class, 'myOrders']);
        Route::get('/{id}', [OrderController::class, 'orderDetails']);
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
    });

    /*
    |----------------------------------------
    | DEBUG (REMOVE IN PRODUCTION)
    |----------------------------------------
    */
    Route::get('/debug-user', function () {
        return response()->json([
            'user' => auth()->user()
        ]);
    });

    /*
    |----------------------------------------
    | Admin Dashboard 
    |----------------------------------------
    */
    

    Route::prefix('admin')->group(function () {
    Route::get('/total-users', [DashboardController::class, 'totalUsers']);
    Route::get('/total-orders', [DashboardController::class, 'totalOrders']);
    Route::get('/total-revenue', [DashboardController::class, 'totalRevenue']);
    Route::get('/top-products', [DashboardController::class, 'topProducts']);
    });
    });