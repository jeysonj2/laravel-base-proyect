<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserLockoutController extends Controller
{
    /**
     * Get all locked users.
     * 
     * @return JsonResponse
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
     * @param User $user
     * @param Request $request
     * @return JsonResponse
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
