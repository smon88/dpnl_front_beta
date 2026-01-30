<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class AdminSocketTokenController extends Controller
{
    public function issue(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'not_authenticated'], 401);
        }

        $user = Auth::user();
        $adminId = $user->backend_uid ?? $user->id;
       
        /** @var \Illuminate\Http\Client\Response $resp */
        $resp = Http::withHeaders([
            'X-SHARED-SECRET' => env('LARAVEL_SHARED_SECRET'),
            'X-Admin-Id' => (string) $adminId,
        ])->post(env('NODE_BACKEND_URL') . '/api/admin/issue-token');

        if (!$resp->ok()) {
            return response()->json(['error' => 'token_issue_failed'], 500);
        }

        return response()->json(['token' => $resp->json('token')]);
    }

}
