<?php

use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Multi-Vendor API is running.']);
});

Route::get('/orders/{id}/invoice', [AdminController::class, 'generateInvoice'])->name('admin.invoice');