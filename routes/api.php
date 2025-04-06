<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;

Route::middleware(['auth:api'])->group(function () {
    // ---------------------------------------------------------------------
    // Define routes that require authentorization per HTTP method, e.g., for admin users
    // You can use the 'role' middleware to restrict access based on user roles
    // The role name is passed to the middleware after the 'role:' prefix
    // For example, to restrict access to admin users:
    // Route::middleware(['role:admin'])->group(function () {
    //     Route::post('roles', [RoleController::class, 'store']);
    //     Route::put('roles/{role}', [RoleController::class, 'update']);
    //     Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    // });
    // Route::apiResource('roles', RoleController::class)->except(['store', 'update', 'destroy']);
    // ---------------------------------------------------------------------

    // Define routes that require authentication including all HTTP methods
    // The role name is passed to the middleware after the 'role:' prefix
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/resend-verification', [EmailVerificationController::class, 'resend']);
    });
});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('verify-email', [EmailVerificationController::class, 'verify']);
