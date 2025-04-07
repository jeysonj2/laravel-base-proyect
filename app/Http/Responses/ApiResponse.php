<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Create a success response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(string $message = 'Operation successful', $data = null, int $statusCode = 200): JsonResponse
    {
        return self::make($statusCode, $message, $data);
    }

    /**
     * Create an error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = 'An error occurred', $data = null, int $statusCode = 400): JsonResponse
    {
        return self::make($statusCode, $message, $data);
    }

    /**
     * Create a not found response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound(string $message = 'Resource not found', $data = null): JsonResponse
    {
        return self::make(404, $message, $data);
    }

    /**
     * Create an unauthorized response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        return self::make(401, $message, $data);
    }

    /**
     * Create a validation error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validationError(string $message = 'Validation failed', $data = null): JsonResponse
    {
        return self::make(422, $message, $data);
    }

    /**
     * Create a forbidden response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden', $data = null): JsonResponse
    {
        return self::make(403, $message, $data);
    }

    /**
     * Create a server error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function serverError(string $message = 'Server error', $data = null): JsonResponse
    {
        return self::make(500, $message, $data);
    }

    /**
     * Make the JSON response.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
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
