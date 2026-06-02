<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->withCount(['listings']);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->boolean('banned_only')) {
            $query->whereNotNull('banned_at');
        }

        $users = $query->latest()->paginate(20);

        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->loadCount(['listings', 'transactions']);
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'balance' => 'sometimes|numeric|min:0',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user->tokens()->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé']);
    }

    public function ban(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Impossible de bannir un administrateur.'], 422);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous bannir vous-même.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'banned_at' => now(),
            'banned_reason' => $validated['reason'] ?? null,
        ]);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Utilisateur banni',
            'user' => $user->fresh(),
        ]);
    }

    public function unban(User $user)
    {
        $user->update([
            'banned_at' => null,
            'banned_reason' => null,
        ]);

        return response()->json([
            'message' => 'Bannissement levé',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
