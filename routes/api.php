<?php

use App\Http\Controllers\Api\V1\Admin\BomController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\StockController;
use App\Http\Controllers\Api\V1\Admin\StockMutationController;
use App\Http\Controllers\Api\V1\Admin\UnitController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\WarehouseController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('admin')->group(function () {
            Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view,sanctum');
            Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create,sanctum');
            Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view,sanctum');
            Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.update,sanctum');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete,sanctum');

            Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view,sanctum');
            Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create,sanctum');
            Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view,sanctum');
            Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update,sanctum');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete,sanctum');

            Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view,sanctum');
            Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create,sanctum');
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete,sanctum');
        });

        Route::prefix('inventory')->group(function () {
            Route::get('warehouses', [WarehouseController::class, 'index'])->middleware('permission:warehouses.view,sanctum');
            Route::post('warehouses', [WarehouseController::class, 'store'])->middleware('permission:warehouses.create,sanctum');
            Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->middleware('permission:warehouses.view,sanctum');
            Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware('permission:warehouses.update,sanctum');
            Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('permission:warehouses.delete,sanctum');

            Route::get('categories', [CategoryController::class, 'index'])->middleware('permission:categories.view,sanctum');
            Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:categories.create,sanctum');
            Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('permission:categories.view,sanctum');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update,sanctum');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete,sanctum');

            Route::get('units', [UnitController::class, 'index'])->middleware('permission:units.view,sanctum');
            Route::post('units', [UnitController::class, 'store'])->middleware('permission:units.create,sanctum');
            Route::get('units/{unit}', [UnitController::class, 'show'])->middleware('permission:units.view,sanctum');
            Route::put('units/{unit}', [UnitController::class, 'update'])->middleware('permission:units.update,sanctum');
            Route::delete('units/{unit}', [UnitController::class, 'destroy'])->middleware('permission:units.delete,sanctum');

            Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode'])->middleware('permission:products.view,sanctum');
            Route::get('products/{product}/boms', [BomController::class, 'getByProduct'])->middleware('permission:boms.view,sanctum');
            Route::get('products', [ProductController::class, 'index'])->middleware('permission:products.view,sanctum');
            Route::post('products', [ProductController::class, 'store'])->middleware('permission:products.create,sanctum');
            Route::get('products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view,sanctum');
            Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update,sanctum');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete,sanctum');

            Route::get('scan/{barcode}', [StockController::class, 'scan'])->middleware('permission:stocks.view,sanctum');
            Route::get('stocks', [StockController::class, 'index'])->middleware('permission:stocks.view,sanctum');
            Route::get('stocks/{stock}', [StockController::class, 'show'])->middleware('permission:stocks.view,sanctum');

            Route::get('mutations', [StockMutationController::class, 'index'])->middleware('permission:mutations.view,sanctum');
            Route::post('mutations', [StockMutationController::class, 'store'])->middleware('permission:mutations.create,sanctum');
            Route::get('mutations/{mutation}', [StockMutationController::class, 'show'])->middleware('permission:mutations.view,sanctum');
        });

        Route::prefix('production')->group(function () {
            Route::get('boms', [BomController::class, 'index'])->middleware('permission:boms.view,sanctum');
            Route::post('boms', [BomController::class, 'store'])->middleware('permission:boms.create,sanctum');
            Route::get('boms/{bom}', [BomController::class, 'show'])->middleware('permission:boms.view,sanctum');
            Route::put('boms/{bom}', [BomController::class, 'update'])->middleware('permission:boms.update,sanctum');
            Route::delete('boms/{bom}', [BomController::class, 'destroy'])->middleware('permission:boms.delete,sanctum');
        });
    });
});
