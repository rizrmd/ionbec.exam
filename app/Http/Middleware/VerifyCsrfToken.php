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
    ];

    /**
     * Determine if the request should be excluded from CSRF verification.
     */
    protected function inExceptArray($request)
    {
        // Allow DEMO token requests to bypass CSRF
        if ($request->is('exam') && $request->input('token') === 'DEMO') {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
