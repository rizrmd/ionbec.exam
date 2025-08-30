<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicSanctumStatefulDomains
{
    /**
     * Handle an incoming request.
     * Dynamically adds the current request's host to Sanctum's stateful domains
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $currentHost = $request->getHost();
        $currentHostWithPort = $request->getHttpHost();
        
        // Get existing stateful domains
        $statefulDomains = config('sanctum.stateful', []);
        
        // Add current host if not already present
        if (!in_array($currentHost, $statefulDomains)) {
            $statefulDomains[] = $currentHost;
        }
        
        if (!in_array($currentHostWithPort, $statefulDomains)) {
            $statefulDomains[] = $currentHostWithPort;
        }
        
        // Update the config at runtime
        config(['sanctum.stateful' => $statefulDomains]);
        
        return $next($request);
    }
}