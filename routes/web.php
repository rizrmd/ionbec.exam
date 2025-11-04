<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - Fallback for debugging
|--------------------------------------------------------------------------
*/

// This is a fallback route file to help debug routing issues
// The main routes are handled by dentro/yalr package

// Custom logout route for proper domain redirect
Route::post('/logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect($request->getSchemeAndHttpHost() . '/login');
})->name('logout');

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

Route::get('/test-group-update', function() {
    $group = \App\Models\Takers\Group::where('hash', '5bzO5NvE')->first();
    return response()->json([
        'message' => 'Test API working',
        'timestamp' => now(),
        'group' => $group ? ['name' => $group->name, 'code' => $group->code] : null,
        'group_found' => $group ? true : false
    ]);
});

Route::get('/debug-route-binding/{hash}', function($hash) {
    try {
        $groupModel = new \App\Models\Takers\Group();
        $group = $groupModel->resolveRouteBinding($hash);
        return response()->json([
            'hash' => $hash,
            'group_found' => $group ? true : false,
            'group' => $group ? ['id' => $group->id, 'name' => $group->name, 'hash' => $group->hash] : null,
            'route_key_name' => $groupModel->getRouteKeyName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'hash' => $hash,
            'error' => $e->getMessage(),
            'route_key_name' => (new \App\Models\Takers\Group())->getRouteKeyName()
        ], 500);
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
Route::get('/', function (Illuminate\Http\Request $request) {
    // Check if Inertia page exists, otherwise show debug info
    if (class_exists(\Inertia\Inertia::class)) {
        try {
            $client = $request->attributes->get('client');
            
            return \Inertia\Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
                'phpVersion' => PHP_VERSION,
                'client' => $client ? [
                    'name' => $client->name,
                    'logo_url' => $client->logo_url,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return 'Inertia error: ' . $e->getMessage();
        }
    }
    return 'Root route from web.php is working! Laravel ' . app()->version();
})->middleware('web');

// Catch-all route for debugging (exclude auth routes)
Route::fallback(function () {
    $path = request()->path();
    
    // Don't override auth routes - let Fortify handle them
    $authRoutes = ['login', 'register', 'password/reset', 'password/confirm', 'email/verify'];
    if (in_array($path, $authRoutes) || str_starts_with($path, 'password/') || str_starts_with($path, 'email/')) {
        abort(404);
    }
    
    return response()->json([
        'message' => 'Route not found, but Laravel is working',
        'path' => $path,
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

// API Routes
Route::get('/api/questions/{questionHash}/attempts', [App\Http\Controllers\Api\QuestionAttemptController::class, 'getAttempts'])
    ->name('api.questions.attempts');

// Exam Logging API - For frontend tab detection
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/exam/log-multiple-tabs', [App\Http\Controllers\ExamLogController::class, 'store']);
});

// Admin Routes - TEMPORARILY OPEN for debugging
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/exam-logs', [App\Http\Controllers\ExamLogController::class, 'index'])->name('exam-logs');
    Route::get('/exam-logs/export', [App\Http\Controllers\ExamLogController::class, 'export'])->name('exam-logs.export');
    Route::get('/exam-logs/{log}', [App\Http\Controllers\ExamLogController::class, 'show'])->name('exam-logs.show');
    Route::delete('/exam-logs/{log}', [App\Http\Controllers\ExamLogController::class, 'destroy'])->name('exam-logs.delete');
});

// TEMPORARY: Protected AdminOnly routes (commented out)
// Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/exam-logs', [App\Http\Controllers\ExamLogController::class, 'index'])->name('exam-logs');
//     Route::get('/exam-logs/export', [App\Http\Controllers\ExamLogController::class, 'export'])->name('exam-logs.export');
//     Route::get('/exam-logs/{log}', [App\Http\Controllers\ExamLogController::class, 'show'])->name('exam-logs.show');
//     Route::delete('/exam-logs/{log}', [App\Http\Controllers\ExamLogController::class, 'destroy'])->name('exam-logs.delete');
// });

// DEBUG: Temporary route without middleware for testing
Route::get('/debug-admin', [App\Http\Controllers\ExamLogController::class, 'debugAdmin'])->name('debug.admin');