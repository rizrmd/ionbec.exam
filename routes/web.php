<?php

use Illuminate\Support\Facades\Route;

// Debug: Log that this file is being loaded
if (!defined('WEB_ROUTES_LOADED')) {
    define('WEB_ROUTES_LOADED', true);
    error_log('WEB ROUTES FILE IS BEING LOADED');
}

/*
|--------------------------------------------------------------------------
| Web Routes - Fallback for debugging
|--------------------------------------------------------------------------
*/

// This is a fallback route file to help debug routing issues
// The main routes are handled by dentro/yalr package

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
        'env' => config('app.env'),
        'url' => config('app.url'),
    ]);
});

// Test route to verify routing works
Route::get('/test', function () {
    return 'Laravel routing is working!';
});