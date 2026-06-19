<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStudent
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403, 'Access denied. Students only.');
        }
        return $next($request);
    }
}