<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function show(Request $request)
    {
        $key = env('ADMIN_SESSION_KEY', 'admin_authenticated');
        if ($request->session()->get($key)) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => ['required','string','min:6'],
        ]);

        $key = env('ADMIN_SESSION_KEY', 'admin_authenticated');

        // Recomendado: usar hash en .env
        $hash = env('ADMIN_PASSWORD_HASH');
        if (!$hash) {
            abort(500, 'ADMIN_PASSWORD_HASH no está configurado en .env');
        }

        if (!Hash::check($request->password, $hash)) {
            return back()->withErrors(['password' => 'Clave inválida.']);
        }

        $request->session()->regenerate();

         // ✅ Marca sesión admin
        $request->session()->put($key, true);

        // ✅ ID del admin (puede ser fijo si solo hay 1 admin)
        $request->session()->put('admin_id', env('ADMIN_ID', 'admin'));


        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $key = env('ADMIN_SESSION_KEY', 'admin_authenticated');
        $request->session()->forget([$key, 'admin_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}