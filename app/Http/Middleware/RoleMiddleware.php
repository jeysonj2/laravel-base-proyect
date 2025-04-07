<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Middleware
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
     * Returns a 403 Forbidden response if the user doesn't have the required role.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request
     * @param  \Closure  $next  The next middleware in the pipeline
     * @param  string  $role  The role name required to access the route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Check if the user is authenticated and has the required role
        if (!$user || strtolower($user->role->name ?? '') !== strtolower($role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
