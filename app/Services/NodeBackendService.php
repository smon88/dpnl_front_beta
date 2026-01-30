<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodeBackendService
{
    private string $baseUrl;
    private string $sharedSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.node_backend.url', 'http://localhost:3005');
        $this->sharedSecret = config('services.node_backend.shared_secret', '');
    }

    /**
     * Sincroniza usuario con el backend Node
     */
    public function syncUser(User $user, string $action = 'create'): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-SHARED-SECRET' => $this->sharedSecret,
            ])->timeout(10)->post("{$this->baseUrl}/api/panel-users/sync", [
                'laravelId' => $user->id,
                'username' => $user->username,
                'alias' => $user->alias,
                'tgUsername' => $user->tg_user ? ltrim($user->tg_user, '@') : null,
                'role' => $user->role,
                'action' => $action,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['panelUser'] ?? null;
            }

            Log::error('NodeBackendService::syncUser failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('NodeBackendService::syncUser exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Solicita envío de OTP via Telegram
     */
    public function requestOtp(User $user): array
    {
        try {
            $response = Http::withHeaders([
                'X-SHARED-SECRET' => $this->sharedSecret,
            ])->timeout(10)->post("{$this->baseUrl}/api/panel-users/request-otp", [
                'laravelId' => $user->id,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('NodeBackendService::requestOtp failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al solicitar OTP',
            ];
        } catch (\Exception $e) {
            Log::error('NodeBackendService::requestOtp exception', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error de conexión con el servidor',
            ];
        }
    }

    /**
     * Valida OTP y obtiene JWT para WebSocket
     */
    public function verifyOtp(User $user, string $otp): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-SHARED-SECRET' => $this->sharedSecret,
            ])->timeout(10)->post("{$this->baseUrl}/api/panel-users/verify-otp", [
                'laravelId' => $user->id,
                'otp' => $otp,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['valid'] ?? false) {
                    return $data;
                }
            }

            Log::warning('NodeBackendService::verifyOtp failed', [
                'userId' => $user->id,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('NodeBackendService::verifyOtp exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
