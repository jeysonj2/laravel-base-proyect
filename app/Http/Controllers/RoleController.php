<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Role Controller.
 * 
 * Handles CRUD operations for user roles in the system.
 * This controller is typically restricted to administrators.
 */
class RoleController extends Controller
{
    /**
     * Display a listing of all roles.
     * 
     * Retrieves and returns all roles defined in the system.
     *
     * @return \Illuminate\Http\JsonResponse Response containing all roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return $this->successResponse('Roles retrieved successfully', $roles);
    }

    /**
     * Store a newly created role in the database.
     * 
     * Validates and creates a new role with the provided name.
     * Uses case-insensitive uniqueness validation for the role name.
     *
     * @param  \Illuminate\Http\Request  $request  Request containing the role data
     * @return \Illuminate\Http\JsonResponse Response containing the newly created role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|case_insensitive_unique:roles,name',
        ]);

        $role = Role::create($validated);
        return $this->successResponse('Role created successfully', $role, 201);
    }

    /**
     * Display the specified role.
     * 
     * Retrieves and returns a specific role by its ID.
     *
     * @param  string  $id  The ID of the role to retrieve
     * @return \Illuminate\Http\JsonResponse Response containing the requested role
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        return $this->successResponse('Role retrieved successfully', $role);
    }

    /**
     * Update the specified role in the database.
     * 
     * Validates and updates an existing role with the provided data.
     * Uses case-insensitive uniqueness validation for the role name,
     * excluding the current role from the check.
     *
     * @param  \Illuminate\Http\Request  $request  Request containing the updated role data
     * @param  string  $id  The ID of the role to update
     * @return \Illuminate\Http\JsonResponse Response containing the updated role
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|case_insensitive_unique:roles,name,' . $id,
        ]);

        $role = Role::findOrFail($id);
        $role->update($validated);
        return $this->successResponse('Role updated successfully', $role);
    }

    /**
     * Remove the specified role from the database.
     * 
     * Deletes a role by its ID.
     *
     * @param  string  $id  The ID of the role to delete
     * @return \Illuminate\Http\JsonResponse Response indicating successful deletion
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $this->successResponse('Role deleted successfully');
    }
}
