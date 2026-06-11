<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Étape 1 : Login basique. 
     * Vérifie le mot de passe et génère le code 2FA.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Vérification de l'utilisateur et du mot de passe
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Les identifiants sont incorrects'], 401);
        }

        // Génération d'un code à 6 chiffres
        $code = rand(100000, 999999);
        
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // TODO : Vous pourrez envoyer $code par email/SMS à ce moment-là
        // Mail::to($user->email)->send(new SendCodeMail($code));

        return response()->json([
            'message' => 'Un code de vérification vous a été envoyé.',
            // Seulement pour le test, on renvoie le code côté frontend. En production, RETIREZ cette ligne !
            'debug_code' => $code 
        ], 200);
    }

    /**
     * Étape 2 : Vérification du code 2FA et génération du Token Sanctum.
     */
    public function verify2fa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        // Vérification si le code correspond
        if ($user->two_factor_code !== $request->code) {
            return response()->json(['message' => 'Le code de vérification est incorrect'], 401);
        }

        // Vérification si le code n'est pas expiré
        if (Carbon::parse($user->two_factor_expires_at)->isPast()) {
            return response()->json(['message' => 'Le code de vérification a expiré'], 401);
        }

        // Le code est bon, on le réinitialise
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // Création du vrai Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    /**
     * Déconnexion (révoque le token).
     */
    public function logout(Request $request)
    {
        // Supprime le token avec lequel l'utilisateur s'est connecté
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }
}
