<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

class RootController extends Controller
{
    public function index(Request $request): Response
    {
        $client = $request->attributes->get('client');
        
        // If no client found (main domain), try to get a default client or use system defaults
        if (!$client) {
            // Try to find the first active client as default, or create default values
            $defaultClient = \App\Models\Client::where('is_active', true)->first();
            if ($defaultClient) {
                $client = $defaultClient;
            }
        }
        
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'client' => $client ? [
                'name' => $client->name,
                'logo_url' => $client->logo_url,
            ] : null,
        ]);
    }
}
