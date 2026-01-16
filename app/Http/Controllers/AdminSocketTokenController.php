<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class AdminSocketTokenController extends Controller
{
    public function issue(Request $request)
    {   
        
        $adminKey = env('ADMIN_SESSION_KEY', 'admin_authenticated');
        if (!$request->session()->get($adminKey)) {
            return response()->json(['error' => 'not_authenticated'], 401);
        }

        $adminId = $request->session()->get('admin_id', env('ADMIN_ID', 'admin-1'));
       
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
