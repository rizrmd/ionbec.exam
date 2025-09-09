<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

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
            'host' => $request->getHost(),
            'path' => $request->path(),
            'is_inertia' => $request->header('X-Inertia'),
            'expects_json' => $request->expectsJson(),
            'user_id' => auth()->id(),
            'user_client_id' => auth()->user()?->client_id,
        ]);

        $home = '/back-office/dashboard';

        // Always redirect to dashboard after login
        return redirect($home);
    }
}