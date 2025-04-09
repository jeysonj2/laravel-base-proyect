<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Role Middleware.
 *
 * This middleware restricts access to routes based on user roles.
 * It is registered with the alias 'role' in bootstrap/app.php.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifies that the authenticated user has the required role to access the route.
     * The comparison is case-insensitive to make role checking more flexible.
     * Throws an AccessDeniedHttpException if the user doesn't have any of the required roles.
     * Supports multiple roles by separating them with commas.
     *
     * @param Request $request            The incoming request
     * @param Closure $next               The next middleware in the pipeline
     * @param string  $role               The primary role required to access the route
     * @param string  ...$additionalRoles Additional roles that are allowed to access the route
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next, string $role, ...$additionalRoles): Response
    {
        // Get authenticated user either from request or from Auth facade
        $user = $request->user() ?? auth()->user();

        // If no authenticated user, throw exception
        if (! $user) {
            throw new AccessDeniedHttpException('User not authenticated.');
        }

        // Get the current user's role name
        $userRoleName = strtolower($user->role->name);

        // Combine the primary role with any additional roles
        $acceptedRoles = array_merge([$role], $additionalRoles);

        // Process the roles array to handle comma-separated roles
        $processedRoles = [];
        foreach ($acceptedRoles as $acceptedRole) {
            // Check if the role contains commas
            if (strpos($acceptedRole, ',') !== false) {
                // Split by comma and merge into processed roles
                $commaSeparatedRoles = array_map('trim', explode(',', $acceptedRole));
                $processedRoles = array_merge($processedRoles, $commaSeparatedRoles);
            } else {
                $processedRoles[] = $acceptedRole;
            }
        }

        // Check if the user has any of the required roles
        foreach ($processedRoles as $acceptedRole) {
            if ($userRoleName === strtolower(trim($acceptedRole))) {
                return $next($request);
            }
        }

        // If we get here, the user doesn't have any of the required roles
        throw new AccessDeniedHttpException('User does not have the required role.');
    }
}
