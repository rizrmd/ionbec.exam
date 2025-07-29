<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ExamMiddleware
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
        if (! Session::get('exam')) {
            return redirect()->route('root');
        }

        return $next($request);
    }
}
