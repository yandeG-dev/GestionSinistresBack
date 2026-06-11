<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * L'Admin crée un Assureur ou un Expert
     */
    public function createProfessional(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:Assureur,Expert',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $motDePasseAleatoire = Str::random(10);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($motDePasseAleatoire),
            'role' => $request->role,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
        ]);

        return response()->json([
            'message' => 'Le profil professionel ('.$user->role.') a été créé avec succès.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user' => $user
        ], 201);
    }

    /**
     * L'Assureur crée un Assuré (Client)
     */
    public function createAssure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $motDePasseAleatoire = Str::random(10);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($motDePasseAleatoire),
            'role' => 'Assure',
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
        ]);

        return response()->json([
            'message' => 'Le compte Assuré a été créé avec succès par l\'assureur.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user' => $user
        ], 201);
    }
}
