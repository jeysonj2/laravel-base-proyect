<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * User Lockout Controller.
 * 
 * Manages user account lockouts, providing functionality to list
 * locked user accounts and unlock specific accounts.
 * This controller is typically accessible only to administrators.
 */
class UserLockoutController extends Controller
{
    /**
     * Get all locked users.
     * 
     * Retrieves a list of all user accounts that are currently locked,
     * either temporarily or permanently.
     *
     * @return \Illuminate\Http\JsonResponse Response containing the list of locked users
     */
    public function index(): JsonResponse
    {
        $lockedUsers = User::where(function($query) {
            $query->where('is_permanently_locked', true)
                ->orWhere(function($query) {
                    $query->whereNotNull('locked_until')
                        ->where('locked_until', '>', now());
                });
        })->get();

        return $this->successResponse('Locked users retrieved successfully.', $lockedUsers);
    }

    /**
     * Unlock a user account.
     * 
     * Removes the lock from a user account, allowing the user to log in again.
     * Can optionally reset the lockout counter to prevent rapid re-locking.
     *
     * @param  \App\Models\User  $user  The user to unlock
     * @param  \Illuminate\Http\Request  $request  Request potentially containing reset_lockout_count parameter
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlock(User $user, Request $request): JsonResponse
    {
        $resetLockoutCount = $request->input('reset_lockout_count', true);
        
        if (!$user->isLockedOut()) {
            return $this->errorResponse('This account is not locked.', null, 400);
        }

        $user->unlock($resetLockoutCount);

        return $this->successResponse('User account unlocked successfully.', $user);
    }
}
