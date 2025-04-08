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
 * This route serves as the entry point for the web application.
 * It redirects users to the API documentation page.
 */
Route::get('/', function () {
    // return view('welcome', ['appName' => env('APP_NAME', 'Laravel Application')]);

    // Redirect to the API documentation page
    return redirect('/api/documentation');
})->name('home');
