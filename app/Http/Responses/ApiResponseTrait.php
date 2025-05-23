<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * API Response Trait.
 *
 * This trait provides convenient methods for controllers to return
 * standardized JSON responses. It acts as a wrapper around the ApiResponse class,
 * making the response methods available directly within controllers.
 *
 * When used in a controller, it allows for consistent API responses
 * across the entire application with minimal code duplication.
 */
trait ApiResponseTrait
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     */
    protected function successResponse(string $message = 'Operation successful', $data = null, int $statusCode = 200): JsonResponse
    {
        return ApiResponse::success($message, $data, $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param mixed $data
     */
    protected function errorResponse(string $message = 'An error occurred', $data = null, int $statusCode = 400): JsonResponse
    {
        return ApiResponse::error($message, $data, $statusCode);
    }

    /**
     * Return a not found response.
     *
     * @param mixed $data
     */
    protected function notFoundResponse(string $message = 'Resource not found', $data = null): JsonResponse
    {
        return ApiResponse::notFound($message, $data);
    }

    /**
     * Return an unauthorized response.
     *
     * @param mixed $data
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        return ApiResponse::unauthorized($message, $data);
    }

    /**
     * Return a validation error response.
     *
     * @param mixed $data
     */
    protected function validationErrorResponse(string $message = 'Validation failed', $data = null): JsonResponse
    {
        return ApiResponse::validationError($message, $data);
    }

    /**
     * Return a forbidden response.
     *
     * @param mixed $data
     */
    protected function forbiddenResponse(string $message = 'Forbidden', $data = null): JsonResponse
    {
        return ApiResponse::forbidden($message, $data);
    }

    /**
     * Return a server error response.
     *
     * @param mixed $data
     */
    protected function serverErrorResponse(string $message = 'Server error', $data = null): JsonResponse
    {
        return ApiResponse::serverError($message, $data);
    }
}
