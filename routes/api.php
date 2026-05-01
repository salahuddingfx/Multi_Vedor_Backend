<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API v1 - Storefront Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/{site}')->group(function () {
    // Initialization
    Route::get('/init', [SiteController::class, 'init']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/track/{tracking_id}', [OrderController::class, 'track']);
});

/*
|--------------------------------------------------------------------------
| Admin API - Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Authentication
    Route::post('/login', [AdminController::class, 'login']);
    
    // Stats
    Route::get('/stats', [AdminController::class, 'getStats']);

    // Products
    Route::get('/products', [AdminController::class, 'getProducts']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    
    // Categories
    Route::get('/categories', [AdminController::class, 'getCategories']);
    
    // Orders
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    
    // Site Settings
    Route::put('/sites/{id}/settings', [AdminController::class, 'updateSettings']);

    // Admin User Management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
});
