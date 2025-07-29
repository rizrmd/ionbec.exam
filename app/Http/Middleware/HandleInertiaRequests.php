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
            'auth.user' => static fn () => $request->user()?->load('roles'),
            'auth.taker' => static fn () => auth()->guard('taker')->user(),
            'jetstream.flash' => static fn () => [
                'style' => $request->session()->get('_executed', false) ? 'success' : 'danger',
                'banner' => $request->session()->get('_message'),
            ],
        ]);
    }
}
