<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AuthenticateTaker
{
    /**
     * Handle an incoming request.
     *
     * @param Request                                                                                           $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, \Closure $next)
    {
        if (! auth()->guard('taker')->check()) {
            return redirect()->route('taker.login');
        }

        return $next($request);
    }
}
