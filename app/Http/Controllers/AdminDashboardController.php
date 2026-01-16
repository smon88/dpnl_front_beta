<?php

namespace App\Http\Controllers;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'nodeUrl' => env('NODE_BACKEND_URL')
        ]);
    }
}