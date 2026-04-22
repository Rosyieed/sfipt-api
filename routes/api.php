<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

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
});
