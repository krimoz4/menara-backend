<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('menara_kpi_token')->plainTextToken;

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'target' => "Connexion réussie de l utilisateur '{$user->name}' ({$user->email})"
            ]);

            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Identifiants incorrects'], 401);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'target' => "Déconnexion de l utilisateur '{$user->name}'"
            ]);
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Déconnexion réussie et jeton révoqué'
        ]);
    }
}