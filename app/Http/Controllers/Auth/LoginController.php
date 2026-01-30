<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NodeBackendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __construct(
        private NodeBackendService $nodeBackend
    ) {}

    /**
     * Muestra formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Paso 1: Valida username + password, solicita OTP
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Credenciales incorrectas.',
            ])->withInput($request->only('username'));
        }

        if (!$user->isActive()) {
            return back()->withErrors([
                'username' => 'Tu cuenta está desactivada.',
            ]);
        }

        // Sincronizar con backend si no tiene backend_uid
        if (!$user->backend_uid) {
            $panelUser = $this->nodeBackend->syncUser($user, 'create');
            if ($panelUser) {
                $user->update([
                    'backend_uid' => $panelUser['id'],
                    'tg_linked' => $panelUser['tgLinked'] ?? false,
                ]);
            }
        }

        // Verificar si tiene Telegram vinculado
        if (!$user->hasTelegramLinked()) {
            return back()->withErrors([
                'username' => 'Debes vincular tu Telegram primero. Envía /start al bot.',
            ]);
        }

        // Solicitar OTP
        $otpResponse = $this->nodeBackend->requestOtp($user);

        if (!($otpResponse['success'] ?? false)) {
            return back()->withErrors([
                'username' => $otpResponse['message'] ?? 'Error al enviar OTP.',
            ]);
        }

        // Guardar user_id pendiente de 2FA
        $request->session()->put('pending_2fa_user', $user->id);
        $request->session()->put('pending_2fa_expires', now()->addMinutes(5));

        Log::info('Login paso 1 exitoso', [
            'user' => $user->username,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login.otp');
    }

    /**
     * Muestra formulario de OTP
     */
    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('pending_2fa_user')) {
            return redirect()->route('login');
        }

        // Verificar expiración
        $expires = $request->session()->get('pending_2fa_expires');
        if ($expires && now()->greaterThan($expires)) {
            $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);
            return redirect()->route('login')->withErrors([
                'username' => 'Sesión expirada. Inicia sesión nuevamente.',
            ]);
        }

        return view('auth.otp');
    }

    /**
     * Paso 2: Valida OTP con el backend Node
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('pending_2fa_user');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);
            return redirect()->route('login');
        }

        // Verificar OTP con backend
        $result = $this->nodeBackend->verifyOtp($user, $request->otp);

        if (!$result) {
            Log::warning('2FA fallido', [
                'user' => $user->username,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'otp' => 'Código incorrecto o expirado.',
            ]);
        }

        // Limpiar sesión pendiente
        $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);

        // Login exitoso
        Auth::login($user);
        $request->session()->regenerate();

        // Guardar token del backend para WebSocket
        if (isset($result['token'])) {
            $request->session()->put('node_token', $result['token']);
        }

        // Actualizar último login
        $user->update(['last_login_at' => now()]);

        Log::info('2FA exitoso', [
            'user' => $user->username,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
