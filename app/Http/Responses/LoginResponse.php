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
        $domain = $request->getHost();
        $user = auth()->user();
        
        \Log::info('LoginResponse::toResponse called', [
            'host' => $domain,
            'path' => $request->path(),
            'is_inertia' => $request->header('X-Inertia'),
            'expects_json' => $request->expectsJson(),
            'user_id' => $user?->id,
            'user_client_id' => $user?->client_id,
        ]);

        // Check if this is a client domain
        $client = \App\Models\Client::findByDomain($domain);
        
        if ($client && $user && $user->client_id === $client->id) {
            // User logging in on their client domain - redirect to root
            // Client domains have limited routes, so redirect to home page
            $home = '/';
        } else {
            // Default back-office login
            $home = '/back-office/dashboard';
        }

        \Log::info('LoginResponse redirect decision', [
            'domain' => $domain,
            'client_found' => $client ? $client->name : null,
            'user_client_match' => $client && $user ? $user->client_id === $client->id : false,
            'redirect_to' => $home,
            'full_url' => $request->getSchemeAndHttpHost() . $home
        ]);

        // For client domains, use full URL to ensure we stay on the correct domain
        if ($client && $user && $user->client_id === $client->id) {
            $fullUrl = $request->getSchemeAndHttpHost() . $home;
            return redirect($fullUrl);
        }

        // Default back-office redirect
        return redirect($home);
    }
}