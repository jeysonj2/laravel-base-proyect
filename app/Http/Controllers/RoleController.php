<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all roles",
     *     description="Retrieves a list of all roles (admin only)",
     *     operationId="getRoles",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of roles",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/Role")
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
     * @return JsonResponse Response containing all roles
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
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create new role",
     *     description="Creates a new role (admin only)",
     *     operationId="createRole",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="manager")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Role"
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
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The name has already been taken.")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing the role data
     *
     * @return JsonResponse Response containing the newly created role
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
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Get role by ID",
     *     description="Retrieves a specific role by its ID (admin only)",
     *     operationId="getRole",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role details",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Role retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Role"
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
     *         description="Role not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     *
     * @param string $id The ID of the role to retrieve
     *
     * @return JsonResponse Response containing the requested role
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
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Update role",
     *     description="Updates an existing role (admin only)",
     *     operationId="updateRole",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="editor")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Role"
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
     *         description="Role not found",
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
     *             @OA\Property(property="message", type="string", example="The name has already been taken.")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing the updated role data
     * @param string  $id      The ID of the role to update
     *
     * @return JsonResponse Response containing the updated role
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
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Delete role",
     *     description="Deletes an existing role (admin only)",
     *     operationId="deleteRole",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
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
     *         description="Role not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     *
     * @param string $id The ID of the role to delete
     *
     * @return JsonResponse Response indicating successful deletion
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return $this->successResponse('Role deleted successfully');
    }
}
