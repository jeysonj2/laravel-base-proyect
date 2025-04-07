<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use ApiResponseTrait;
}
