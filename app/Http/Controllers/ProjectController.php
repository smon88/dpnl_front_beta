<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\NodeBackendService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected NodeBackendService $nodeBackend;

    public function __construct(NodeBackendService $nodeBackend)
    {
        $this->nodeBackend = $nodeBackend;
    }

    /**
     * Lista de proyectos (Admin)
     */
    public function index()
    {
        $projects = Project::withCount([
            'users as approved_count' => fn($q) => $q->wherePivot('status', 'approved'),
            'users as pending_count' => fn($q) => $q->wherePivot('status', 'pending'),
        ])->orderBy('created_at', 'desc')->get();

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Formulario de creación (Admin)
     */
    public function create()
    {
        return view('admin.projects.create');
    }

    /**
     * Guardar nuevo proyecto (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $project = Project::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        // Sync con Node backend
        $nodeProject = $this->nodeBackend->syncProject($project, 'create');
        if ($nodeProject) {
            $project->update(['backend_uid' => $nodeProject['id']]);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyecto creado correctamente');
    }

    /**
     * Ver proyecto con miembros (Admin)
     */
    public function show(Project $project)
    {
        $project->load(['users' => fn($q) => $q->orderByPivot('created_at', 'desc')]);

        $approvedUsers = $project->users->where('pivot.status', 'approved');
        $pendingUsers = $project->users->where('pivot.status', 'pending');

        return view('admin.projects.show', compact('project', 'approvedUsers', 'pendingUsers'));
    }

    /**
     * Formulario de edición (Admin)
     */
    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Actualizar proyecto (Admin)
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $project->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Sync con Node backend
        $this->nodeBackend->syncProject($project, 'update');

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Proyecto actualizado correctamente');
    }

    /**
     * Eliminar proyecto (Admin)
     */
    public function destroy(Project $project)
    {
        // Sync delete con Node backend
        $this->nodeBackend->syncProject($project, 'delete');

        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyecto eliminado correctamente');
    }

    /**
     * Asignar usuario a proyecto (Admin)
     */
    public function assignUser(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,useradmin,user',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Verificar si ya existe la asignación
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'El usuario ya está asignado a este proyecto');
        }

        // Asignar directamente como aprobado
        $project->users()->attach($user->id, [
            'role' => $validated['role'],
            'status' => 'approved',
        ]);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, $validated['role'], 'approved', 'add');

        return back()->with('success', 'Usuario asignado correctamente');
    }

    /**
     * Aprobar solicitud de acceso (Admin)
     */
    public function approveUser(Project $project, User $user)
    {
        $pivot = $project->users()->where('user_id', $user->id)->first();
        if (!$pivot) {
            return back()->with('error', 'El usuario no tiene solicitud pendiente');
        }

        $project->users()->updateExistingPivot($user->id, ['status' => 'approved']);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, $pivot->pivot->role, 'approved', 'update');

        return back()->with('success', 'Usuario aprobado correctamente');
    }

    /**
     * Rechazar solicitud de acceso (Admin)
     */
    public function rejectUser(Project $project, User $user)
    {
        $pivot = $project->users()->where('user_id', $user->id)->first();
        if (!$pivot) {
            return back()->with('error', 'El usuario no tiene solicitud pendiente');
        }

        $project->users()->updateExistingPivot($user->id, ['status' => 'rejected']);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, $pivot->pivot->role, 'rejected', 'update');

        return back()->with('success', 'Solicitud rechazada');
    }

    /**
     * Remover usuario de proyecto (Admin)
     */
    public function removeUser(Project $project, User $user)
    {
        $pivot = $project->users()->where('user_id', $user->id)->first();
        if (!$pivot) {
            return back()->with('error', 'El usuario no está en este proyecto');
        }

        $project->users()->detach($user->id);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, $pivot->pivot->role, $pivot->pivot->status, 'remove');

        return back()->with('success', 'Usuario removido del proyecto');
    }

    /**
     * Cambiar rol de usuario (Admin)
     */
    public function updateUserRole(Request $request, Project $project, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:owner,useradmin,user',
        ]);

        $pivot = $project->users()->where('user_id', $user->id)->first();
        if (!$pivot) {
            return back()->with('error', 'El usuario no está en este proyecto');
        }

        $project->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, $validated['role'], $pivot->pivot->status, 'update');

        return back()->with('success', 'Rol actualizado correctamente');
    }

    // ============ USER METHODS ============

    /**
     * Proyectos disponibles para solicitar acceso (User)
     */
    public function available()
    {
        $user = auth()->user();

        // Proyectos activos donde el usuario NO está
        $projects = Project::where('is_active', true)
            ->whereDoesntHave('users', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        return view('projects.available', compact('projects'));
    }

    /**
     * Mis proyectos (User)
     */
    public function myProjects()
    {
        $user = auth()->user();

        $approvedProjects = $user->approvedProjects()->get();
        $pendingProjects = $user->pendingProjects()->get();

        return view('projects.my', compact('approvedProjects', 'pendingProjects'));
    }

    /**
     * Solicitar acceso a proyecto (User)
     */
    public function requestAccess(Project $project)
    {
        $user = auth()->user();

        // Verificar si ya existe solicitud
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Ya tienes una solicitud para este proyecto');
        }

        // Crear solicitud pendiente
        $project->users()->attach($user->id, [
            'role' => 'user',
            'status' => 'pending',
        ]);

        // Sync con Node backend
        $this->nodeBackend->syncProjectMember($project, $user, 'user', 'pending', 'add');

        return redirect()->route('projects.my')
            ->with('success', 'Solicitud enviada correctamente');
    }
}
