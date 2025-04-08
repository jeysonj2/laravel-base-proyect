<?php

/**
 * Bootstrap the application.
 * 
 * This file is the entry point for the Laravel application and configures
 * the core components, including routing, middleware, and exception handling.
 *
 * @package Laravel
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Register API routes
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Register custom middleware aliases.
         * This allows the 'role' middleware to be used in route definitions.
         */
        $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Register custom exception renderers.
         * This ensures that all exceptions are rendered as JSON responses
         * to maintain a consistent API response format.
         */
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                return response()->json([
                    'code' => 403,
                    'message' => $e->getMessage() ?: 'Forbidden. You do not have the required permissions.',
                ], 403);
            }
            
            return response()->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage() ?: 'An error occurred.',
                'data' => method_exists($e, 'getData') ? $e->getData() : null,
            ], $e->getCode() ?: 500);
        });
    })->create();
