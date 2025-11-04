<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access admin area.');
        }

        // Debug logging
        \Log::info('AdminOnly middleware check', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'is_admin' => auth()->user()->is_admin,
            'admin_role' => auth()->user()->admin_role,
        ]);

        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            \Log::warning('Admin access denied', [
                'user_email' => auth()->user()->email,
                'is_admin' => auth()->user()->is_admin,
                'admin_role' => auth()->user()->admin_role,
            ]);

            abort(403, 'Access Denied. Administrator privileges required. User is_admin: ' . auth()->user()->is_admin);
        }

        \Log::info('Admin access granted', [
            'user_email' => auth()->user()->email,
            'admin_role' => auth()->user()->admin_role,
        ]);

        return $next($request);
    }
}