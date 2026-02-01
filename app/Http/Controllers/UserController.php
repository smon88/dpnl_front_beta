<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NodeBackendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private NodeBackendService $nodeBackend
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;

        $users = User::orderBy('created_at', 'desc')->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('users._table', compact('users'))->render(),
                'pagination' => view('components.pagination', ['paginator' => $users])->render(),
            ]);
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'alias' => 'nullable|string|max:255',
            'tg_user' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'alias' => $validated['alias'],
            'tg_user' => $validated['tg_user'],
            'role' => 'user',
            'is_active' => true,
        ]);

        // Sincronizar con backend Node
        $panelUser = $this->nodeBackend->syncUser($user, 'create');
        if ($panelUser) {
            $user->update([
                'backend_uid' => $panelUser['id'],
                'tg_linked' => $panelUser['tgLinked'] ?? false,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'alias' => 'nullable|string|max:255',
            'tg_user' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'username' => $validated['username'],
            'alias' => $validated['alias'],
            'tg_user' => $validated['tg_user'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sincronizar con backend Node
        $action = $user->is_active ? 'update' : 'deactivate';
        $this->nodeBackend->syncUser($user, $action);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'No puedes eliminar al administrador.']);
        }

        // Desactivar en backend Node
        $this->nodeBackend->syncUser($user, 'deactivate');

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
