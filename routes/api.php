<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API v1 - Storefront Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/{site}')->middleware('throttle:api')->group(function () {
    // Initialization
    Route::get('/init', [SiteController::class, 'init']);
    Route::get('/version', [SiteController::class, 'version']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders');
    Route::get('/orders/track/{tracking_id}', [OrderController::class, 'track']);
    
    // Contact
    Route::post('/contact', [ContactController::class, 'store']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Coupons
    Route::post('/validate-coupon', [CouponController::class, 'validateCoupon']);

    // SEO
    Route::get('/sitemap', [App\Http\Controllers\Api\SEOController::class, 'generateSitemap']);

    // Push Notifications
    Route::post('/push-subscribe', [NotificationController::class, 'subscribePush']);
});

/*
|--------------------------------------------------------------------------
| Admin API - Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('throttle:admin')->group(function () {
    // Public Admin Routes
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:login');
    
    // Protected Admin Routes
    Route::middleware(['auth:sanctum', 'working_hours'])->group(function () {
        Route::get('/me', [AdminController::class, 'me']);
        // Stats
        Route::get('/stats', [AdminController::class, 'getStats']);

    // Products
    Route::get('/products', [AdminController::class, 'getProducts']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::post('/products/{id}/update', [AdminController::class, 'updateProduct']);
    Route::post('/products/{id}/delete', [AdminController::class, 'deleteProduct']);
    
    // Categories
    Route::get('/categories', [AdminController::class, 'getCategories']);
    Route::post('/categories', [AdminController::class, 'storeCategory']);
    Route::post('/categories/{id}/update', [AdminController::class, 'updateCategory']);
    Route::post('/categories/{id}/delete', [AdminController::class, 'deleteCategory']);
    
    // Orders
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::post('/inventory/return', [AdminController::class, 'recordReturn']);
    Route::get('/inventory/returns', [AdminController::class, 'getReturns']);
    Route::get('/sales/stats', [AdminController::class, 'getSalesStats']);
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::post('/orders/{id}/payment-status', [AdminController::class, 'updatePaymentStatus']);
    Route::post('/orders/{id}/update', [AdminController::class, 'updateOrder']);
    Route::post('/orders/{id}/delete', [AdminController::class, 'deleteOrder']);
    
    // Hero Slides
    Route::get('/hero-slides', [AdminController::class, 'getHeroSlides']);
    Route::post('/hero-slides', [AdminController::class, 'storeHeroSlide']);
    Route::post('/hero-slides/{id}', [AdminController::class, 'updateHeroSlide']); // Use POST for multipart/form-data update
    Route::post('/hero-slides/{id}/delete', [AdminController::class, 'deleteHeroSlide']);
    
    
    // Site Settings
    Route::get('/sites/{id}/settings', [AdminController::class, 'getSettings']);
    Route::post('/sites/{id}/settings/update', [AdminController::class, 'updateSettings']);
    Route::post('/settings/upload', [AdminController::class, 'uploadSettingsMedia']);

    // Contact Messages
    Route::get('/messages', [AdminController::class, 'getMessages']);
    Route::post('/messages/{id}/read', [AdminController::class, 'markMessageRead']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'getAdminReviews']);
    Route::post('/reviews/{id}/update', [ReviewController::class, 'updateAdminReview']);
    Route::post('/reviews/{id}/delete', [ReviewController::class, 'deleteAdminReview']);

    // Admin User & Customer Management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::post('/users/{id}/update', [AdminController::class, 'updateUser']);
    Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser']);
    Route::get('/customers', [AdminController::class, 'getCustomers']);

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons/{coupon}', [CouponController::class, 'show']);
    Route::post('/coupons/{coupon}/update', [CouponController::class, 'update']);
    Route::post('/coupons/{coupon}/delete', [CouponController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'destroy']);
    });
});
