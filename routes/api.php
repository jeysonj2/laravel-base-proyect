<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserLockoutController;

Route::middleware(['auth:api'])->group(function () {
    // ---------------------------------------------------------------------
    // Define routes that require authentication per HTTP method, e.g., for admin users
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
        
        // User lockout management routes (admin only)
        Route::get('locked-users', [UserLockoutController::class, 'index']);
        Route::post('users/{user}/unlock', [UserLockoutController::class, 'unlock']);
    });
    
    // Routes available to any authenticated user
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('verify-email', [EmailVerificationController::class, 'verify']);

// Password reset routes
Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);
