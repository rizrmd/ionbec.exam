<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'logout',  // Avoid 419 on sign-out in the current multi-domain Inertia flow
        'api/exam/log-multiple-tabs',
    ];

    /**
     * Determine if the request should be excluded from CSRF verification.
     */
    protected function inExceptArray($request)
    {
        if (
            app()->environment(['local', 'testing']) &&
            config('app.enable_demo_token', false) &&
            $request->is('exam') &&
            $request->input('token') === config('app.demo_token', 'DEMO')
        ) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
