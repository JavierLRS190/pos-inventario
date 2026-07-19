<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\WarehouseController;

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Productos — cualquier usuario autenticado puede ver
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Solo admin puede crear/editar/eliminar
    Route::middleware('role:admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });
    // Ventas — cajero y admin pueden crear
Route::get('/sales', [SaleController::class, 'index']);
Route::get('/sales/today', [SaleController::class, 'todaySummary']);
Route::get('/sales/{id}', [SaleController::class, 'show']);
Route::middleware('role:admin,cajero')->group(function () {
    Route::post('/sales', [SaleController::class, 'store']);
});
Route::middleware('role:admin')->group(function () {
    Route::patch('/sales/{id}/cancel', [SaleController::class, 'cancel']);
});
Route::get('/warehouses', [WarehouseController::class, 'index']);


// Movimientos de bodega — bodeguero y admin
Route::get('/stock-movements', [StockMovementController::class, 'index']);
Route::middleware('role:admin,bodeguero')->group(function () {
    Route::post('/stock-movements', [StockMovementController::class, 'store']);
});

Route::middleware('role:admin')->prefix('reports')->group(function () {
    Route::get('/sales',        [ReportController::class, 'salesByRange']);
    Route::get('/top-products', [ReportController::class, 'topProducts']);
    Route::get('/cash-cut',     [ReportController::class, 'cashCut']);
    Route::get('/stock',        [ReportController::class, 'stockReport']);
});
    // Categorías — todos pueden ver
    Route::get('/categories', [CategoryController::class, 'index']);
});