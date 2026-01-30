<?php

namespace App\Http\Controllers;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.pages.dashboard');
    }

    public function profile()
    {
        return view('admin.pages.profile');
    }

    public function traffic()
    {
        return view('admin.pages.traffic');
    }

    public function tools()
    {
        return view('admin.pages.tools');
    }

    public function records()
    {
        return view('admin.pages.records');
    }

    public function settings()
    {
        return view('admin.pages.settings');
    }
}