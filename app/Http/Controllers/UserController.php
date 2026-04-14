<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tramite;
use App\Models\Ventanilla;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'tramites', 'ventanilla'])->latest()->get();
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Form', [
            'roles' => Role::all(),
            'tramites' => Tramite::orderBy('nombre')->get(),
            'ventanillas' => Ventanilla::orderBy('nombre')->get(),
            'user' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', Password::defaults()],
            'ventanilla_id' => 'nullable|exists:ventanillas,id',
            'role' => 'required|exists:roles,name',
            'tramites' => 'nullable|array',
            'tramites.*' => 'exists:tramites,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'ventanilla_id' => $request->ventanilla_id,
        ]);

        $user->assignRole($request->role);

        if ($request->has('tramites')) {
            $user->tramites()->sync($request->tramites);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'tramites', 'ventanilla']);
        return Inertia::render('Users/Form', [
            'roles' => Role::all(),
            'tramites' => Tramite::orderBy('nombre')->get(),
            'ventanillas' => Ventanilla::orderBy('nombre')->get(),
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class.',email,'.$user->id,
            'password' => ['nullable', Password::defaults()],
            'ventanilla_id' => 'nullable|exists:ventanillas,id',
            'role' => 'required|exists:roles,name',
            'tramites' => 'nullable|array',
            'tramites.*' => 'exists:tramites,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'ventanilla_id' => $request->ventanilla_id,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        if ($request->has('tramites')) {
            $user->tramites()->sync($request->tramites);
        } else {
            $user->tramites()->detach();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->withErrors(['message' => 'No puedes eliminarte a ti mismo.']);
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
