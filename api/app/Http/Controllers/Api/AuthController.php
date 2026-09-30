<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,vendeur,technicien',
            'genre' => 'nullable|in:homme,femme,autre',
            'telephone' => 'required|string',
            'adresse' => 'required|string',
            'photo' => 'nullable|image|max:4096',
            'conditions' => 'accepted',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('avatars', 'public');
        }

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'genre' => $validated['genre'] ?? null,
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'],
            'photo' => $photoPath,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$user) {
            $user = User::create([
                'nom' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                'role' => 'client',
            ]);
        } elseif (!$user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return redirect(config('app.frontend_url') . '/auth/callback?token=' . $token);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
            'genre' => 'nullable|in:homme,femme,autre',
            'photo' => 'nullable|image|max:4096',
            'password' => 'nullable|string|min:8|confirmed',
            'current_password' => 'required_with:password|nullable|string',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
            }
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                \Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('avatars', 'public');
        }

        $user->nom = $validated['nom'];
        $user->telephone = $validated['telephone'] ?? $user->telephone;
        $user->adresse = $validated['adresse'] ?? $user->adresse;
        $user->genre = $validated['genre'] ?? $user->genre;
        $user->save();

        return response()->json($user);
    }
}