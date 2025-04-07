<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a success response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message = 'Operation successful', $data = null, int $statusCode = 200): JsonResponse
    {
        return ApiResponse::success($message, $data, $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'An error occurred', $data = null, int $statusCode = 400): JsonResponse
    {
        return ApiResponse::error($message, $data, $statusCode);
    }

    /**
     * Return a not found response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found', $data = null): JsonResponse
    {
        return ApiResponse::notFound($message, $data);
    }

    /**
     * Return an unauthorized response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        return ApiResponse::unauthorized($message, $data);
    }

    /**
     * Return a validation error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse(string $message = 'Validation failed', $data = null): JsonResponse
    {
        return ApiResponse::validationError($message, $data);
    }

    /**
     * Return a forbidden response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden', $data = null): JsonResponse
    {
        return ApiResponse::forbidden($message, $data);
    }

    /**
     * Return a server error response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Server error', $data = null): JsonResponse
    {
        return ApiResponse::serverError($message, $data);
    }
}
