<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/products', [\App\Http\Controllers\API\ProductController::class, 'index']);
    Route::get('/products/{sku}', [\App\Http\Controllers\API\ProductController::class, 'show']);
    Route::post('/stock-movements', [\App\Http\Controllers\API\StockMovementController::class, 'store']);
    Route::get('/warehouses/{id}/stock', [\App\Http\Controllers\API\WarehouseController::class, 'stock']);
    Route::get('/stock-report', [\App\Http\Controllers\API\StockReportController::class, 'index']);
});
