<?php

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
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return response()->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage() ?: 'An error occurred.',
                'data' => method_exists($e, 'getData') ? $e->getData() : null,
            ], $e->getCode() ?: 500);
        });
    })->create();
