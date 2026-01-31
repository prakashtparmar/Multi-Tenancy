<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\AuthController;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| Access via: http://tenant.domain.com/api/v1/...
|
*/

Route::prefix('v1')->group(function () {
    
    // Auth
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // ERP Modules (Read/Write)
        Route::apiResource('products', \App\Http\Controllers\Api\Tenant\ProductController::class);
        Route::apiResource('orders', \App\Http\Controllers\Api\Tenant\OrderController::class);
        Route::apiResource('invoices', \App\Http\Controllers\Api\Tenant\InvoiceController::class);
        Route::apiResource('shipments', \App\Http\Controllers\Api\Tenant\ShipmentController::class);
        Route::apiResource('returns', \App\Http\Controllers\Api\Tenant\OrderReturnController::class);
    });
});
