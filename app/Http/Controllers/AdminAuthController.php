<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NodeBackendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    private NodeBackendService $nodeBackend;

    public function __construct(NodeBackendService $nodeBackend)
    {
        $this->nodeBackend = $nodeBackend;
    }

    public function show(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.pages.login');
    }

    /**
     * Paso 1: Valida username + password, solicita OTP
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
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

        // Sincronizar con backend
        $panelUser = $this->nodeBackend->syncUser($user, $user->backend_uid ? 'update' : 'create');
        if ($panelUser) {
            $user->update([
                'backend_uid' => $panelUser['id'],
                'tg_linked' => $panelUser['tgLinked'] ?? false,
            ]);
            $user->refresh(); // Recargar el modelo con los nuevos valores
        }

        // Verificar si tiene Telegram vinculado
        if (!$user->hasTelegramLinked()) {
            return back()->withErrors([
                'username' => 'Debes vincular tu Telegram primero. Envia /start al bot.',
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

        return redirect()->route('admin.login.otp')
            ->with('success', 'Codigo OTP enviado a tu Telegram.');
    }

    /**
     * Muestra formulario de OTP
     */
    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('pending_2fa_user')) {
            return redirect()->route('admin.login');
        }

        $expires = $request->session()->get('pending_2fa_expires');
        if ($expires && now()->greaterThan($expires)) {
            $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);
            return redirect()->route('admin.login')->withErrors([
                'username' => 'Sesion expirada. Inicia sesion nuevamente.',
            ]);
        }

        return view('admin.pages.otp');
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
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);
            return redirect()->route('admin.login');
        }

        // Rate limiting manual (5 intentos por minuto)
        $rateLimitKey = 'otp_verify:' . $userId;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            Log::warning('2FA rate limited', [
                'user' => $user->username,
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds,
            ]);

            return back()->withErrors([
                'otp' => "Demasiados intentos. Espera {$seconds} segundos.",
            ]);
        }

        // Incrementar contador ANTES de verificar
        RateLimiter::hit($rateLimitKey, 60);

        // Verificar OTP con backend
        $result = $this->nodeBackend->verifyOtp($user, $request->otp);

        if (!$result) {
            Log::warning('2FA fallido', [
                'user' => $user->username,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'otp' => 'Codigo incorrecto o expirado.',
            ]);
        }

        // OTP válido: limpiar rate limiter
        RateLimiter::clear($rateLimitKey);

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

        return redirect()->route('admin.dashboard')
            ->with('success', 'Bienvenido, ' . ($user->alias ?? $user->username) . '!');
    }

    /**
     * Reenvía código OTP (máximo 5 veces por hora)
     */
    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('pending_2fa_user');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget(['pending_2fa_user', 'pending_2fa_expires']);
            return redirect()->route('admin.login');
        }

        // Rate limiting: 5 reenvíos por 60 minutos
        $rateLimitKey = 'otp_resend:' . $userId;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            Log::warning('OTP resend rate limited', [
                'user' => $user->username,
                'ip' => $request->ip(),
                'minutes_remaining' => $minutes,
            ]);

            return back()->withErrors([
                'otp' => "Has alcanzado el limite de reenvios. Espera {$minutes} minutos.",
            ]);
        }

        // Solicitar nuevo OTP
        $otpResponse = $this->nodeBackend->requestOtp($user);

        if (!($otpResponse['success'] ?? false)) {
            return back()->withErrors([
                'otp' => $otpResponse['message'] ?? 'Error al reenviar codigo.',
            ]);
        }

        // Incrementar contador de reenvíos (expira en 60 minutos)
        RateLimiter::hit($rateLimitKey, 3600);

        // Obtener intentos restantes
        $attemptsRemaining = 5 - RateLimiter::attempts($rateLimitKey);

        // Extender tiempo de expiración de la sesión OTP
        $request->session()->put('pending_2fa_expires', now()->addMinutes(5));

        Log::info('OTP reenviado', [
            'user' => $user->username,
            'ip' => $request->ip(),
            'attempts_remaining' => $attemptsRemaining,
        ]);

        return back()->with('success', "Nuevo codigo enviado. Te quedan {$attemptsRemaining} reenvios.");
    }

    /**
     * Obtiene intentos de reenvío restantes (para AJAX)
     */
    public function getResendAttempts(Request $request)
    {
        $userId = $request->session()->get('pending_2fa_user');
        if (!$userId) {
            return response()->json(['error' => 'No session'], 401);
        }

        $rateLimitKey = 'otp_resend:' . $userId;
        $attempts = RateLimiter::attempts($rateLimitKey);
        $remaining = max(0, 5 - $attempts);
        $availableIn = RateLimiter::tooManyAttempts($rateLimitKey, 5)
            ? RateLimiter::availableIn($rateLimitKey)
            : 0;

        return response()->json([
            'remaining' => $remaining,
            'availableIn' => $availableIn,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('info', 'Has cerrado sesion correctamente.');
    }
}