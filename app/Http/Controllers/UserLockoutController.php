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
     * Retrieves a paginated list of all user accounts that are currently locked,
     * either temporarily or permanently.
     *
     * @OA\Get(
     *     path="/api/locked-users",
     *     summary="Get locked users",
     *     description="Retrieves all locked user accounts with pagination (admin only)",
     *     operationId="getLockedUsers",
     *     tags={"User Lockout"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
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
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1,
     *                     description="Current page number"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="first_page_url",
     *                     type="string",
     *                     example="http://example.com/api/locked-users?page=1",
     *                     description="URL for the first page"
     *                 ),
     *                 @OA\Property(
     *                     property="from",
     *                     type="integer",
     *                     example=1,
     *                     description="The starting item index of the current page"
     *                 ),
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer",
     *                     example=2,
     *                     description="The last page number"
     *                 ),
     *                 @OA\Property(
     *                     property="last_page_url",
     *                     type="string",
     *                     example="http://example.com/api/locked-users?page=2",
     *                     description="URL for the last page"
     *                 ),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     description="Navigation links for pagination",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="url", type="string", nullable=true),
     *                         @OA\Property(property="label", type="string"),
     *                         @OA\Property(property="active", type="boolean")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="next_page_url",
     *                     type="string",
     *                     nullable=true,
     *                     example="http://example.com/api/locked-users?page=2",
     *                     description="URL for the next page"
     *                 ),
     *                 @OA\Property(
     *                     property="path",
     *                     type="string",
     *                     example="http://example.com/api/locked-users",
     *                     description="Base path for pagination"
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=15,
     *                     description="Number of items per page"
     *                 ),
     *                 @OA\Property(
     *                     property="prev_page_url",
     *                     type="string",
     *                     nullable=true,
     *                     example=null,
     *                     description="URL for the previous page"
     *                 ),
     *                 @OA\Property(
     *                     property="to",
     *                     type="integer",
     *                     example=15,
     *                     description="The ending item index of the current page"
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=20,
     *                     description="Total number of locked users"
     *                 )
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
     * @param Request $request The HTTP request containing pagination parameters
     *
     * @return JsonResponse Response containing the list of locked users
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100); // Ensure per_page is between 1 and 100

        $lockedUsers = User::where(function ($query) {
            $query->where('is_permanently_locked', true)
                ->orWhere(function ($query) {
                    $query->whereNotNull('locked_until')
                        ->where('locked_until', '>', now());
                });
        })->paginate($perPage);

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
