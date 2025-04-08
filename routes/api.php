<?php

/**
 * API Routes.
 *
 * This file defines all API routes for the application.
 * Routes are organized by authentication requirement and user role.
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLockoutController;
use Illuminate\Support\Facades\Route;

/**
 * Protected routes that require authentication.
 * These routes are accessible only to users with a valid JWT token.
 */
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

    /**
     * Admin-only routes.
     * These routes are restricted to users with the 'admin' role.
     */
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/resend-verification', [EmailVerificationController::class, 'resend']);

        // User lockout management routes (admin only)
        Route::get('locked-users', [UserLockoutController::class, 'index']);
        Route::post('users/{user}/unlock', [UserLockoutController::class, 'unlock']);
    });

    /**
     * User profile routes.
     * These routes are available to any authenticated user regardless of role.
     */
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
});

/**
 * Public routes.
 * These routes are accessible without authentication.
 */
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('verify-email', [EmailVerificationController::class, 'verify']);

/**
 * Password reset routes.
 * These routes handle the password reset flow.
 */
Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);
