<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class AdminSession
{
    public function handle(Request $request, Closure $next)
    {
        $key = env('ADMIN_SESSION_KEY', 'admin_authenticated');

        if (!$request->session()->get($key)) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}