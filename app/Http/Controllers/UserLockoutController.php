<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @OA\Get(
     *     path="/api/locked-users",
     *     summary="Get locked users",
     *     description="Retrieves all locked user accounts (admin only)",
     *     operationId="getLockedUsers",
     *     tags={"User Lockout"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of locked users",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Locked users retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have admin role",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     *
     * @return JsonResponse Response containing the list of locked users
     */
    public function index(): JsonResponse
    {
        $lockedUsers = User::where(function ($query) {
            $query->where('is_permanently_locked', true)
                ->orWhere(function ($query) {
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
     * @OA\Post(
     *     path="/api/users/{user}/unlock",
     *     summary="Unlock user account",
     *     description="Unlocks a locked user account (admin only)",
     *     operationId="unlockUser",
     *     tags={"User Lockout"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID to unlock",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="reset_lockout_count",
     *                 type="boolean",
     *                 example=true,
     *                 description="Whether to reset the lockout counter"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User successfully unlocked",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User account unlocked successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Account not locked",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="This account is not locked.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have admin role",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     *
     * @param User    $user    The user to unlock
     * @param Request $request Request potentially containing reset_lockout_count parameter
     */
    public function unlock(User $user, Request $request): JsonResponse
    {
        $resetLockoutCount = $request->input('reset_lockout_count', true);

        if (! $user->isLockedOut()) {
            return $this->errorResponse('This account is not locked.', null, 400);
        }

        $user->unlock($resetLockoutCount);

        return $this->successResponse('User account unlocked successfully.', $user);
    }
}
