<?php

/**
 * Web Routes
 * 
 * This file defines all web routes for the application.
 * These are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group.
 *
 * @package Laravel
 */

use Illuminate\Support\Facades\Route;

/**
 * Home page route
 * 
 * Returns the welcome view when accessing the root URL.
 */
Route::get('/', function () {
    return view('welcome', ['appName' => env('APP_NAME', 'Laravel Application')]);
});
