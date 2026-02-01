<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Obtener proyectos para el mapa de nombres
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin ve todos los proyectos
            $projects = Project::select('backend_uid', 'name', 'slug')->get();
        } else {
            // Usuario normal solo ve sus proyectos aprobados
            $projects = $user->approvedProjects()->select('projects.backend_uid', 'projects.name', 'projects.slug')->get();
        }

        // Crear mapa de projectId -> nombre
        $projectsMap = $projects->mapWithKeys(function ($project) {
            return [$project->backend_uid => $project->name];
        })->toArray();

        return view('admin.pages.dashboard', compact('projectsMap'));
    }

    public function profile()
    {
        return view('admin.pages.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'alias' => 'nullable|string|max:50',
        ];

        // Solo validar password si se proporciona
        if ($request->filled('current_password') || $request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = ['required', 'confirmed', Password::min(6)];
        }

        $request->validate($rules);

        // Actualizar alias
        $user->alias = $request->alias;

        // Cambiar contraseña si se proporcionó
        if ($request->filled('password')) {
            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'La contraseña actual es incorrecta.',
                ])->withInput();
            }

            $user->password = $request->password;
        }

        $user->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
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

    public function userRecords()
    {
        return view('admin.pages.user-records');
    }
}