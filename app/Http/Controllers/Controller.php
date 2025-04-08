<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller class for the application.
 * 
 * This abstract class extends Laravel's base controller and incorporates
 * the API response trait to provide standardized JSON responses across
 * all controllers in the application.
 * 
 * @OA\Info(
 *     title="Laravel Base Project API",
 *     version="1.0.0",
 *     description="API documentation for Laravel Base Project",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     description="API Server",
 *     url=L5_SWAGGER_CONST_HOST
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller extends BaseController
{
    use ApiResponseTrait;
}
