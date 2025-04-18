<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * API Response Helper.
 *
 * This class provides a standardized way to create consistent JSON responses
 * across the application. It offers static methods for common response types
 * such as success, error, not found, etc., ensuring that all API responses
 * follow the same format with code, message, and optional data.
 */
class ApiResponse
{
    /**
     * Create a success response.
     *
     * @param mixed $data
     */
    public static function success(string $message = 'Operation successful', $data = null, int $statusCode = 200): JsonResponse
    {
        return self::make($statusCode, $message, $data);
    }

    /**
     * Create an error response.
     *
     * @param mixed $data
     */
    public static function error(string $message = 'An error occurred', $data = null, int $statusCode = 400): JsonResponse
    {
        return self::make($statusCode, $message, $data);
    }

    /**
     * Create a not found response.
     *
     * @param mixed $data
     */
    public static function notFound(string $message = 'Resource not found', $data = null): JsonResponse
    {
        return self::make(404, $message, $data);
    }

    /**
     * Create an unauthorized response.
     *
     * @param mixed $data
     */
    public static function unauthorized(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        return self::make(401, $message, $data);
    }

    /**
     * Create a validation error response.
     *
     * @param mixed $data
     */
    public static function validationError(string $message = 'Validation failed', $data = null): JsonResponse
    {
        return self::make(422, $message, $data);
    }

    /**
     * Create a forbidden response.
     *
     * @param mixed $data
     */
    public static function forbidden(string $message = 'Forbidden', $data = null): JsonResponse
    {
        return self::make(403, $message, $data);
    }

    /**
     * Create a server error response.
     *
     * @param mixed $data
     */
    public static function serverError(string $message = 'Server error', $data = null): JsonResponse
    {
        return self::make(500, $message, $data);
    }

    /**
     * Make the JSON response.
     *
     * @param mixed $data
     */
    private static function make(int $code, string $message, $data = null): JsonResponse
    {
        $response = [
            'code' => $code,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Conditionally include debug information in development
        if (config('app.debug') && $code >= 400 && isset($data['exception-message'])) {
            $response['debug'] = $data['exception-message'];
            unset($data['exception-message']);
        }

        return response()->json($response, $code);
    }
}
