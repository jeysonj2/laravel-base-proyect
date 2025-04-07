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
 */
abstract class Controller extends BaseController
{
    use ApiResponseTrait;
}
