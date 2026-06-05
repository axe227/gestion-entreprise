<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ── REGISTER ──
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255|min:2',
            'email'    => 'required|email|unique:users|max:255',
            'password' => 'required|min:6|max:100' // ✅ min 6 au lieu de 4
        ]);

        $user = User::create([
            'name'     => strip_tags($request->name), // ✅ XSS protection
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user'
        ]);

        $token = $user->createToken('gestovia-token')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    // ── LOGIN avec Rate Limiting ──
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        // ✅ Rate limiting — max 5 tentatives par IP par minute
        $key = 'login.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes."
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60); // ✅ incrémente le compteur
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // ✅ Reset rate limiter si succès
        RateLimiter::clear($key);

        // ✅ Révoque les anciens tokens avant d'en créer un nouveau
        $user->tokens()->delete();

        $token = $user->createToken('gestovia-token', ['*'], now()->addDays(7))->plainTextToken;

        $data = $user->toArray();
        $data['avatar_url'] = $user->avatar
            ? asset('storage/' . $user->avatar)
            : null;

        return response()->json(['user' => $data, 'token' => $token]);
    }

    // ── LOGOUT ──
    public function logout(Request $request)
    {
        // ✅ Révoque uniquement le token actuel
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }

    // ── ME ──
    public function me(Request $request)
    {
        $user = $request->user();
        $data = $user->toArray();
        $data['avatar_url'] = $user->avatar
            ? asset('storage/' . $user->avatar)
            : null;
        return response()->json($data);
    }

    // ── UPDATE PROFIL ──
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name'  => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        $user->update([
            'name'  => strip_tags($request->name), // ✅ XSS
            'email' => $request->email
        ]);
        return response()->json(['user' => $user]);
    }

    // ── UPDATE MOT DE PASSE ──
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|max:100',
        ]);
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }
        $user->update(['password' => Hash::make($request->new_password)]);

        // ✅ Révoque tous les tokens après changement de mdp
        $user->tokens()->delete();
        $newToken = $user->createToken('gestovia-token')->plainTextToken;

        return response()->json([
            'message' => 'Mot de passe mis à jour',
            'token'   => $newToken
        ]);
    }

    // ── UPLOAD AVATAR ──
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        $user = $request->user();
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }
        // ✅ Nom de fichier sécurisé
        $filename = Str::uuid() . '.' . $request->file('avatar')->getClientOriginalExtension();
        $path     = $request->file('avatar')->storeAs('avatars', $filename, 'public');
        $user->update(['avatar' => $path]);
        return response()->json([
            'message' => 'Avatar mis à jour',
            'avatar'  => asset('storage/' . $path)
        ]);
    }

    // ── DELETE AVATAR ──
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }
        $user->update(['avatar' => null]);
        return response()->json(['message' => 'Avatar supprimé']);
    }
}