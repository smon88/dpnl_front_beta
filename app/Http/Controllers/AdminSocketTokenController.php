<?php

namespace App\Http\Controllers;

use App\Services\NodeBackendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AdminSocketTokenController extends Controller
{
    protected NodeBackendService $nodeBackend;

    public function __construct(NodeBackendService $nodeBackend)
    {
        $this->nodeBackend = $nodeBackend;
    }

    public function issue(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'not_authenticated'], 401);
        }

        $user = Auth::user();

        // Si el usuario no tiene backend_uid, sincronizar con Node
        if (empty($user->backend_uid)) {
            $panelUser = $this->nodeBackend->syncUser($user, 'create');
            if ($panelUser && isset($panelUser['id'])) {
                $user->update(['backend_uid' => $panelUser['id']]);
                $user->refresh();
            }
        }

        $panelUserId = $user->backend_uid ?? '';
        $panelRole = strtoupper($user->role ?? 'USER'); // ADMIN o USER

        if (empty($panelUserId)) {
            return response()->json(['error' => 'user_not_synced'], 500);
        }

        /** @var \Illuminate\Http\Client\Response $resp */
        $resp = Http::withHeaders([
            'X-SHARED-SECRET' => env('LARAVEL_SHARED_SECRET'),
            'X-Panel-User-Id' => (string) $panelUserId,
            'X-Panel-Role' => $panelRole,
        ])->post(env('NODE_BACKEND_URL') . '/api/admin/issue-token');

        if (!$resp->ok()) {
            return response()->json(['error' => 'token_issue_failed'], 500);
        }

        return response()->json(['token' => $resp->json('token')]);
    }
}
