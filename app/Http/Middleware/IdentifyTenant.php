<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $domain = $request->getHost();
        
        // Find client by domain
        $client = Client::findByDomain($domain);
        
        if (!$client) {
            // If no client found, check if this is the main domain for root access
            $mainDomain = config('app.main_domain', env('APP_URL'));
            
            // Extract host from APP_URL if it's a full URL
            $mainHost = parse_url($mainDomain, PHP_URL_HOST) ?: $mainDomain;
            
            // Allow access if:
            // 1. Domain matches the main domain exactly
            // 2. Domain is localhost/127.0.0.1
            // 3. Domain ends with .local (for local development)
            // 4. No client check (for initial setup)
            $isMainDomain = ($domain === $mainHost) || 
                           ($domain === 'localhost') || 
                           ($domain === '127.0.0.1') ||
                           str_ends_with($domain, '.local') ||
                           str_ends_with($domain, '.sslip.io') ||
                           str_ends_with($domain, '.coolify.io') ||
                           str_ends_with($domain, '.internal') ||
                           (strpos($domain, ':') !== false && strpos($domain, 'localhost') === 0);
            
            if (!$isMainDomain) {
                abort(404, 'Client not found for this domain');
            }
            
            // This is the main domain, allow access for root users
            if (Auth::check() && !Auth::user()->hasRole('root')) {
                abort(403, 'Access denied. Root access required for main domain.');
            }
        } else {
            // Store client in the request and session
            $request->attributes->set('client', $client);
            session(['client_id' => $client->id]);
            
            // Set the client for the authenticated user
            if (Auth::check()) {
                $user = Auth::user();
                
                // Verify user belongs to this client (skip check for login/logout routes)
                $isAuthRoute = in_array($request->path(), ['login', 'logout']);
                if (!$isAuthRoute && $user->client_id && $user->client_id !== $client->id) {
                    Auth::logout();
                    return redirect('/login')->with('error', 'You do not have access to this client.');
                }
                
                // Set the current client context
                app()->singleton('current_client', function () use ($client) {
                    return $client;
                });
            }
        }
        
        return $next($request);
    }
}
