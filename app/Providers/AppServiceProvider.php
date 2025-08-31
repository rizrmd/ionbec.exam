<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
                \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL
            );
            
            // Force HTTPS scheme
            \URL::forceScheme('https');
        }
        
        // Check for HTTPS from proxy headers
        if (request()->header('X-Forwarded-Proto') === 'https') {
            \URL::forceScheme('https');
        }
        
        // Use APP_URL if set, otherwise dynamically determine
        if (config('app.url')) {
            \URL::forceRootUrl(config('app.url'));
        } elseif (request()->getHttpHost()) {
            $scheme = request()->secure() || request()->header('X-Forwarded-Proto') === 'https' ? 'https' : 'http';
            $appUrl = $scheme . '://' . request()->getHttpHost();
            config(['app.url' => $appUrl]);
            \URL::forceRootUrl($appUrl);
        }
    }
}
