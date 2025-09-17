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
        $examSession = Session::get('exam');
        
        if (! $examSession) {
            \Log::info('ExamMiddleware: No exam session found', [
                'session_id' => Session::getId(),
                'url' => $request->url(),
                'method' => $request->method()
            ]);
            return redirect('/');
        }
        
        \Log::info('ExamMiddleware: Exam session found', [
            'has_taker' => isset($examSession['taker']),
            'has_delivery' => isset($examSession['delivery'])
        ]);

        return $next($request);
    }
}
