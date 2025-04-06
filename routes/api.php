<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

Route::apiResource('roles', RoleController::class);
Route::apiResource('users', UserController::class);
