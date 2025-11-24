<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
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
        $roles = Role::all();
        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }

    public function edit(User $user): Response
    {
        $roles = Role::all();
        return Inertia::render('Users/Edit', [
            'usuario' => $user->load('role'),
            'roles' => $roles,
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

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
