<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->is_admin) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
