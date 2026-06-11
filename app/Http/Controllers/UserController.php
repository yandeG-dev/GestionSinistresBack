<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            'password' => 'required|string|min:8',
            'role' => 'required|in:Assureur,Expert', // Seuls ces deux rôles peuvent être créés ici
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
        ]);

        return response()->json([
            'message' => 'Le profil professionel ('.$user->role.') a été créé avec succès.',
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
            'password' => 'required|string|min:8',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Assure', // Verrouillé sur "Assure" !
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
        ]);

        return response()->json([
            'message' => 'Le compte Assuré a été créé avec succès par l\'assureur.',
            'user' => $user
        ], 201);
    }
}
