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
        $home = Fortify::redirects('login') ?? '/back-office/dashboard';

        // For API requests, return JSON
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return new JsonResponse(['two_factor' => false], 200);
        }

        // For Inertia and regular web requests, always redirect
        return redirect()->intended($home);
    }
}