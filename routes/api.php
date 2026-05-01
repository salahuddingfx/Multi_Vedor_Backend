<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CouponController;

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
    
    // Contact
    Route::post('/contact', [ContactController::class, 'store']);

    // Dynamic Pages
    Route::get('/pages/{slug}', [SiteController::class, 'getPage']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Coupons
    Route::post('/validate-coupon', [CouponController::class, 'validateCoupon']);
});

/*
|--------------------------------------------------------------------------
| Admin API - Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Public Admin Routes
    Route::post('/login', [AdminController::class, 'login']);
    
    // Protected Admin Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Stats
        Route::get('/stats', [AdminController::class, 'getStats']);

    // Products
    Route::get('/products', [AdminController::class, 'getProducts']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
    
    // Categories
    Route::get('/categories', [AdminController::class, 'getCategories']);
    Route::post('/categories', [AdminController::class, 'storeCategory']);
    Route::post('/categories/{id}', [AdminController::class, 'updateCategory']); // For multipart updates
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory']);
    
    // Orders
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::put('/orders/{id}/payment-status', [AdminController::class, 'updatePaymentStatus']);
    
    // Hero Slides
    Route::get('/hero-slides', [AdminController::class, 'getHeroSlides']);
    Route::post('/hero-slides', [AdminController::class, 'storeHeroSlide']);
    Route::post('/hero-slides/{id}', [AdminController::class, 'updateHeroSlide']); // Use POST for multipart/form-data update
    Route::delete('/hero-slides/{id}', [AdminController::class, 'deleteHeroSlide']);
    
    // Dynamic Pages
    Route::get('/pages', [AdminController::class, 'getPages']);
    Route::post('/pages', [AdminController::class, 'storePage']);
    Route::put('/pages/{id}', [AdminController::class, 'updatePage']);
    Route::delete('/pages/{id}', [AdminController::class, 'deletePage']);
    
    // Site Settings
    Route::get('/sites/{id}/settings', [AdminController::class, 'getSettings']);
    Route::put('/sites/{id}/settings', [AdminController::class, 'updateSettings']);

    // Contact Messages
    Route::get('/messages', [AdminController::class, 'getMessages']);
    Route::put('/messages/{id}/read', [AdminController::class, 'markMessageRead']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'getAdminReviews']);
    Route::put('/reviews/{id}', [ReviewController::class, 'updateAdminReview']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'deleteAdminReview']);

    // Admin User Management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    // Coupons
    Route::apiResource('coupons', CouponController::class);
    });
});
