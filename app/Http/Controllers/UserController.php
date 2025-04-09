<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * User Controller.
 *
 * Handles CRUD operations for users in the system.
 * This controller is typically restricted to administrators.
 */
class UserController extends Controller
{
    /**
     * Display a listing of all users.
     *
     * Retrieves and returns all users in the system.
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     description="Retrieves a list of all users (admin only)",
     *     operationId="getUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
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
     * @return JsonResponse Response containing all users
     */
    public function index(): JsonResponse
    {
        $users = User::all();

        return $this->successResponse('Users retrieved successfully', $users);
    }

    /**
     * Store a newly created user in the database.
     *
     * Validates and creates a new user with the provided data.
     * Uses case-insensitive uniqueness validation for the email.
     * Triggers the UserCreated event after successful creation.
     *
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create new user",
     *     description="Creates a new user (admin only)",
     *     operationId="createUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "last_name", "email", "password", "role_id"},
     *
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="StrongPassword123!"),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
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
     *         description="Forbidden - User does not have admin role or is trying to create a superadmin without being superadmin",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Only superadmins can create other superadmin users")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The email has already been taken.")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing the user data
     *
     * @return JsonResponse Response containing the newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|case_insensitive_unique:users,email',
            'password' => 'required|string|strong_password',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Check if creating a superadmin user
        $superadminRole = \App\Models\Role::getSuperadminRole();
        if ($superadminRole && $validated['role_id'] == $superadminRole->id) {
            // Check if user has permission to create superadmin
            $response = $this->checkSuperadminPermission('create');
            if ($response !== null) {
                return $response;
            }
        }

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        // Dispatch the UserCreated event
        event(new UserCreated($user));

        return $this->successResponse('User created successfully', $user, 201);
    }

    /**
     * Display the specified user.
     *
     * Retrieves and returns a specific user by ID.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieves a specific user by their ID (admin only)",
     *     operationId="getUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
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
     * @param string $id The ID of the user to retrieve
     *
     * @return JsonResponse Response containing the requested user
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return $this->successResponse('User retrieved successfully', $user);
    }

    /**
     * Update the specified user in the database.
     *
     * Validates and updates an existing user with the provided data.
     * Uses case-insensitive uniqueness validation for the email.
     * If the email is changed, triggers email verification process.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     description="Updates an existing user (admin only)",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="NewStrongPassword123!"),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
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
     *         description="Forbidden - User does not have admin role or trying to modify a superadmin without being superadmin",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Only superadmins can update superadmin users")
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
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The email has already been taken.")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing the updated user data
     * @param string  $id      The ID of the user to update
     *
     * @return JsonResponse Response containing the updated user
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Check if the target user is a superadmin
        if ($user->isSuperadmin) {
            // Check permission to update superadmin
            $response = $this->checkSuperadminPermission('update');
            if ($response !== null) {
                return $response;
            }
        }

        // Check if attempting to change role to superadmin
        $superadminRole = \App\Models\Role::getSuperadminRole();
        if (
            $request->has('role_id') && $superadminRole &&
            $request->role_id == $superadminRole->id &&
            $user->role_id != $superadminRole->id
        ) {
            // Only superadmins can make users into superadmins
            if (! auth()->user()->isSuperadmin) {
                return $this->forbiddenResponse('Only superadmins can assign the superadmin role');
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|case_insensitive_unique:users,email,' . $id,
            'password' => 'sometimes|required|string|strong_password',
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $currentEmail = $user->email;

        $user->update($validated);

        if (isset($validated['email']) && $validated['email'] !== $currentEmail) {
            $user->verification_code = bin2hex(random_bytes(16));
            $user->email_verified_at = null;
            Mail::to($validated['email'])->send(new EmailVerification($user));
            $user->save();

            return $this->successResponse('User updated successfully. Please verify the new email address.', $user);
        }

        return $this->successResponse('User updated successfully', $user);
    }

    /**
     * Remove the specified user from the database.
     *
     * Deletes a user by ID.
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user",
     *     description="Deletes an existing user (admin only)",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
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
     *         description="Forbidden - User does not have admin role, trying to delete a superadmin without being superadmin, or attempting to delete own account",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Only superadmins can delete superadmin users",
     *                 description="Could also be: 'You cannot delete your own account'"
     *             )
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
     * @param string $id The ID of the user to delete
     *
     * @return JsonResponse Response indicating successful deletion
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent users from deleting their own account
        $authId = auth()->user()->id;
        $userId = $user->id;

        if ($authId === $userId) {
            return $this->forbiddenResponse('You cannot delete your own account');
        }

        // Check if the target user is a superadmin
        if ($user->isSuperadmin) {
            // Check permission to delete superadmin
            $response = $this->checkSuperadminPermission('delete');
            if ($response !== null) {
                return $response;
            }
        }

        $user->delete();

        return $this->successResponse('User deleted successfully');
    }

    /**
     * Check if the current user has permission to manage superadmin users.
     *
     * @param string $action The action being performed (create, update, delete)
     *
     * @return JsonResponse|null Returns a forbidden response if not allowed, null if allowed
     */
    private function checkSuperadminPermission(string $action): ?JsonResponse
    {
        if (! auth()->user()->isSuperadmin) {
            return $this->forbiddenResponse(
                "Only superadmins can {$action} superadmin users"
            );
        }

        return null;
    }
}
