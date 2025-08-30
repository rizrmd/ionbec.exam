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
        // Force HTTPS in production if behind a proxy
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        
        // Dynamically set the application URL based on the request
        if (request()->getHttpHost()) {
            $scheme = request()->secure() ? 'https' : 'http';
            $appUrl = $scheme . '://' . request()->getHttpHost();
            config(['app.url' => $appUrl]);
            \URL::forceRootUrl($appUrl);
        }
    }
}
