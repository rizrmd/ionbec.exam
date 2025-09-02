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

Route::get('/test-traefik-config', function () {
    try {
        $service = app(\App\Services\TraefikDomainService::class);
        
        // Test write permissions
        if (!$service->testWrite()) {
            return response()->json(['error' => 'Cannot write to Traefik config directory'], 500);
        }
        
        // Update config
        $service->updateMdxmConfig();
        
        return response()->json([
            'success' => true,
            'message' => 'mdxm.yaml updated successfully',
            'clients' => \App\Models\Client::where('is_active', true)->whereNotNull('domains')->count()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

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

// Add a simple root fallback for debugging
Route::get('/', function () {
    // Check if Inertia page exists, otherwise show debug info
    if (class_exists(\Inertia\Inertia::class)) {
        try {
            return \Inertia\Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
                'phpVersion' => PHP_VERSION,
            ]);
        } catch (\Exception $e) {
            return 'Inertia error: ' . $e->getMessage();
        }
    }
    return 'Root route from web.php is working! Laravel ' . app()->version();
});

// Catch-all route for debugging
Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found, but Laravel is working',
        'path' => request()->path(),
        'method' => request()->method(),
        'all_routes' => collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
            ];
        })->take(10),
    ], 404);
});