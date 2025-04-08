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
     * @param Request $request The incoming request
     * @param Closure $next    The next middleware in the pipeline
     * @param string  $roles   The role name(s) required to access the route (comma-separated)
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Get authenticated user either from request or from Auth facade
        $user = $request->user() ?? auth()->user();

        // If no authenticated user, throw exception
        if (! $user) {
            throw new AccessDeniedHttpException('User not authenticated.');
        }

        // Split roles by comma if multiple roles are specified
        $acceptedRoles = explode(',', $roles);

        // Check if the user has any of the required roles
        foreach ($acceptedRoles as $role) {
            if (strtolower($user->role->name) === strtolower(trim($role))) {
                return $next($request);
            }
        }

        // If we get here, the user doesn't have any of the required roles
        throw new AccessDeniedHttpException('User does not have the required role.');
    }
}
