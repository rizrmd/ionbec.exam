<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Illuminate\Http\JsonResponse;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        \Log::info('LoginResponse::toResponse called', [
            'headers' => $request->headers->all(),
            'is_inertia' => $request->header('X-Inertia'),
            'expects_json' => $request->expectsJson(),
        ]);

        $home = '/back-office/dashboard';

        // Always redirect for Inertia requests
        // Inertia will handle the redirect properly
        return redirect($home);
    }
}