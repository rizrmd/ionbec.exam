<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request)
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app.name' => static fn () => config('app.name'),
            'auth.user' => static fn () => $request->user()?->load(['roles' => function ($query) {
                $query->withoutGlobalScopes();
            }]),
            'auth.taker' => static fn () => auth()->guard('taker')->user(),
            'client' => static fn () => $request->attributes->get('client'),
            'jetstream.flash' => static fn () => [
                'style' => $request->session()->get('_executed', false) ? 'success' : 'danger',
                'banner' => $request->session()->get('_message'),
            ],
            // Share standard Laravel flash messages
            'flash' => static fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
            // Also share errors for validation
            'errors' => static fn () => $request->session()->get('errors', new \Illuminate\Support\MessageBag),
            // Share CSRF token for all requests
            'csrf_token' => static fn () => $request->session()->token(),
        ]);
    }
}
