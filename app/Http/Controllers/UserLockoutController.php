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

        return response()->json([
            'code' => 200,
            'message' => 'Locked users retrieved successfully.',
            'data' => $lockedUsers
        ]);
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
            return response()->json([
                'code' => 400,
                'message' => 'This account is not locked.',
            ], 400);
        }

        $user->unlock($resetLockoutCount);

        return response()->json([
            'code' => 200,
            'message' => 'User account unlocked successfully.',
            'data' => $user
        ]);
    }
}
