<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        // Trust proxies for proper HTTPS detection
        if ($this->app->environment('production')) {
            // Trust all proxies (since we're behind Coolify's proxy)
            request()->setTrustedProxies(
                ['127.0.0.1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO | 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
            );
            
            // Force HTTPS scheme
            \URL::forceScheme('https');
        }
        
        // Check for HTTPS from proxy headers
        if (request()->header('X-Forwarded-Proto') === 'https') {
            \URL::forceScheme('https');
        }
        
        // Dynamic URL generation based on current request domain
        if (request()->getHttpHost()) {
            $scheme = request()->secure() || request()->header('X-Forwarded-Proto') === 'https' ? 'https' : 'http';
            $currentUrl = $scheme . '://' . request()->getHttpHost();
            
            // Check if this is a client domain
            $currentDomain = request()->getHost();
            $client = \App\Models\Client::findByDomain($currentDomain);
            
            if ($client) {
                // For client domains, use the current domain for URL generation
                \URL::forceRootUrl($currentUrl);
                config(['app.url' => $currentUrl]);
            } else {
                // For main domain, use APP_URL if set, otherwise current URL
                $mainUrl = config('app.url') ?: $currentUrl;
                \URL::forceRootUrl($mainUrl);
                config(['app.url' => $mainUrl]);
            }
        }
    }
}
