<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('role')->orderByDesc('usu_id')->get();
        return Inertia::render('Users/Index', [
            'usuarios' => $users,
        ]);
    }

    public function create(): Response
    {
        $roles = Rol::all();
        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        \Log::info('=== CREAR USUARIO - INICIO ===');
        \Log::info('Datos validados:', $validated);
        \Log::info('role_id tipo: ' . gettype($validated['role_id']));
        \Log::info('role_id valor: ' . $validated['role_id']);

        $user = User::create([
            'usu_nombre' => $validated['name'],
            'usu_apellido' => $validated['last_name'] ?? null,
            'usu_email' => $validated['email'],
            'usu_password' => Hash::make($validated['password']),
            'rol_id' => (int) $validated['role_id'],
        ]);

        \Log::info('Usuario creado con ID: ' . $user->usu_id);
        \Log::info('rol_id guardado: ' . $user->rol_id);
        \Log::info('=== CREAR USUARIO - FIN ===');

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }

    public function edit(User $user): Response
    {
        $roles = Rol::all();
        return Inertia::render('Users/Edit', [
            'usuario' => $user->load('role'),
            'roles' => $roles,
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        $data = [
            'usu_nombre' => $validated['name'],
            'usu_apellido' => $validated['last_name'],
            'usu_email' => $validated['email'],
            'rol_id' => (int) $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $data['usu_password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(User $user)
    {
        // No permitir eliminar al usuario actual
        if ($user->getKey() === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente');
    }
}
