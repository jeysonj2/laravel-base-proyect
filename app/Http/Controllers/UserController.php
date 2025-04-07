<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Events\UserCreated;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return $this->successResponse('Users retrieved successfully', $users);
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return $this->successResponse('User retrieved successfully', $user);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $this->successResponse('User deleted successfully');
    }
}
