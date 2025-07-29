<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request                                                                                           $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @param string|null                                                                                       ...$guards
     *
     * @return Response
     */
    public function handle(Request $request, \Closure $next, ...$guards): Response
    {
        foreach ((empty($guards) ? [null] : $guards) as $guard) {
            if (Auth::guard($guard)->check()) {
                if ('web' === $guard) {
                    $roles = Auth::guard($guard)->user()->roles->pluck('slug')->toArray();
                    if (in_array('administrator', $roles)) {
                        return redirect()->route('back-office.dashboard');
                    }

                    return redirect()->route('back-office.scoring.index');
                }
                if ('taker' === $guard) {
                    return redirect()->route('taker.dashboard');
                }

                return redirect('/');
            }
        }

        return $next($request);
    }
}
