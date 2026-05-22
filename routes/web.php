<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SEOController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Multi-Vendor API is running.']);
});

Route::get('/seo/{site}/product/{slug}', [SEOController::class, 'showProductSEO'])->name('product.seo');

Route::get('/orders/{id}/invoice', [AdminController::class, 'generateInvoice'])->name('admin.invoice');
Route::get('/orders/{id}/invoice/download', [AdminController::class, 'downloadInvoice'])->name('admin.invoice.download');

// Email Testing Routes
Route::get('/test-mail/{site_id}/{email}', function ($site_id, $email) {
    $order = \App\Models\Order::with(['items.product', 'site'])
        ->where('site_id', $site_id)
        ->latest()
        ->first();
    if (!$order) return "No order found for site ID: $site_id. Please place an order first.";
    
    \Illuminate\Support\Facades\Notification::route('mail', $email)
        ->notify(new \App\Notifications\CustomerOrderConfirmation($order));
        
    return "Test mail sent to <b>$email</b> using template for <b>" . ($order->site?->name ?? 'Unknown') . "</b>";
});