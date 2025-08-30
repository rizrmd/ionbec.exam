<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RootController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Fallback routes in case Yalr doesn't load properly
Route::get('/', [RootController::class, 'index'])->name('root');
Route::get('/debug', function () {
    return response()->json([
        'status' => 'Laravel routing is working via standard routes',
        'timestamp' => now(),
        'routes_count' => count(Route::getRoutes()),
        'yalr_loaded' => class_exists('\Dentro\Yalr\RouterFactory')
    ]);
});