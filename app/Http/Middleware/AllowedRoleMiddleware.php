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
        // Check if user is authenticated first
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        $roles = array_map(function ($role) {
            return $role['slug'];
        }, $request->user()->load(['roles' => function ($query) {
            $query->withoutGlobalScopes();
        }])->roles->toArray());

        // Root role has access to everything
        if (in_array('root', $roles)) {
            return $next($request);
        }

        $allowed = explode('|', $rolesAllowed);

        if (0 === count(array_intersect($allowed, $roles))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            return redirect()->back();
        }

        return $next($request);
    }
}
