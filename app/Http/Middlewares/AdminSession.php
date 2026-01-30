<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        // Verificar que el usuario esté activo
        if (!Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('admin.login')->withErrors([
                'username' => 'Tu cuenta ha sido desactivada.',
            ]);
        }

        return $next($request);
    }
}