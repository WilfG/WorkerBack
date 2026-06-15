<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'role' => 'required|in:admin,worker,client',
            'status' => 'required|boolean',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $validated['name'] = $validated['firstname'] . ' ' . $validated['lastname'];

        unset($validated['firstname'], $validated['lastname']);
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    public function show(User $user)
    {
        $user->load('workImages');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = [
            'admin' => 'Administrateur',
            'worker' => 'Travailleur',
            'client' => 'Client'
        ];
        $firstname = explode(' ', $user->name)[0] ?? '';
        $lastname = explode(' ', $user->name)[1] ?? '';
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        return view('users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'required|string|max:20',
            'role' => 'required|in:admin,worker,client',
            'status' => 'required|boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $validated['name'] = $validated['firstname'] . ' ' . $validated['lastname'];
        unset($validated['firstname'], $validated['lastname']);
        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a des données associées');
        }
    }
}
