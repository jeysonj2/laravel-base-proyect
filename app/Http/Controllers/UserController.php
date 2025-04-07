<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Events\UserCreated;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;

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
     * @return \Illuminate\Http\JsonResponse Response containing all users
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
     * @param  \Illuminate\Http\Request  $request  Request containing the user data
     * @return \Illuminate\Http\JsonResponse Response containing the newly created user
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
     * @param  string  $id  The ID of the user to retrieve
     * @return \Illuminate\Http\JsonResponse Response containing the requested user
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
     * @param  \Illuminate\Http\Request  $request  Request containing the updated user data
     * @param  string  $id  The ID of the user to update
     * @return \Illuminate\Http\JsonResponse Response containing the updated user
     */
    public function update(Request $request, string $id): JsonResponse
    {
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

        $user = User::findOrFail($id);
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
     * @param  string  $id  The ID of the user to delete
     * @return \Illuminate\Http\JsonResponse Response indicating successful deletion
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $this->successResponse('User deleted successfully');
    }
}
