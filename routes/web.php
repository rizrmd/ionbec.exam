<?php

use Illuminate\Support\Facades\Route;

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