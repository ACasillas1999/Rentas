<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Solo admins pueden gestionar usuarios
        if (! auth()->user()->hasPermission('users.view')) {
            abort(403, 'No tienes permiso para gestionar usuarios.');
        }

        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (! auth()->user()->hasPermission('users.create')) {
            abort(403, 'No tienes permiso para crear usuarios.');
        }

        $properties = Property::orderBy('name')->get();
        $allPermissions = User::ALL_PERMISSIONS;
        $permissionLabels = User::PERMISSION_LABELS;
        $moduleLabels = User::MODULE_LABELS;

        return view('users.create', compact('properties', 'allPermissions', 'permissionLabels', 'moduleLabels'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->hasPermission('users.create')) {
            abort(403, 'No tienes permiso para crear usuarios.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,viewer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:60',
            'allowed_properties' => 'nullable|array',
            'allowed_properties.*' => 'exists:properties,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Guardar permisos personalizados (solo si se proporcionan)
        if ($request->filled('permissions')) {
            foreach ($request->permissions as $permission) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                ]);
            }
        }

        // Guardar restricción de propiedades
        if ($request->filled('allowed_properties')) {
            $user->allowedProperties()->sync($request->allowed_properties);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        if (! auth()->user()->hasPermission('users.edit')) {
            abort(403, 'No tienes permiso para editar usuarios.');
        }

        $properties = Property::orderBy('name')->get();
        $allPermissions = User::ALL_PERMISSIONS;
        $permissionLabels = User::PERMISSION_LABELS;
        $moduleLabels = User::MODULE_LABELS;

        $user->load('userPermissions', 'allowedProperties');
        $userPermissions = $user->userPermissions->pluck('permission')->toArray();
        $userPropertyIds = $user->allowedProperties->pluck('id')->toArray();

        return view('users.edit', compact(
            'user', 'properties', 'allPermissions', 'permissionLabels',
            'moduleLabels', 'userPermissions', 'userPropertyIds'
        ));
    }

    public function update(Request $request, User $user)
    {
        if (! auth()->user()->hasPermission('users.edit')) {
            abort(403, 'No tienes permiso para editar usuarios.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,viewer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:60',
            'allowed_properties' => 'nullable|array',
            'allowed_properties.*' => 'exists:properties,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Actualizar permisos personalizados
        $user->userPermissions()->delete();
        if ($request->filled('permissions')) {
            foreach ($request->permissions as $permission) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                ]);
            }
        }

        // Actualizar restricción de propiedades
        $user->allowedProperties()->sync($request->allowed_properties ?? []);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if (! auth()->user()->hasPermission('users.delete')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
