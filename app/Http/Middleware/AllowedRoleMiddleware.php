<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AllowedRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request                                                                                           $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @param mixed                                                                                             $rolesAllowed
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, \Closure $next, $rolesAllowed)
    {
        $roles = array_map(function ($role) {
            return $role['slug'];
        }, $request->user()->load('roles')->roles->toArray());
        $allowed = explode('|', $rolesAllowed);

        if (0 === count(array_intersect($allowed, $roles))) {
            return redirect()->back();
        }

        return $next($request);
    }
}
