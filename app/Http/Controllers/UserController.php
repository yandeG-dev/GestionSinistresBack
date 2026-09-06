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
            'role' => 'required|in:Administrateur,Agent,Expert,Gestionnaire,Comptable',
            'telephone' => 'required|string|max:20',
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
            'adresse' => 'Non renseignée'
        ]);

        return response()->json([
            'message' => 'Le profil professionel ('.$user->role.') a été créé avec succès.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user' => $user
        ], 201);
    }

    /**
     * L'Assureur crée un Assuré (Client) ET son Contrat
     */
    public function createAssure(Request $request)
    {
        // 1. On valide TOUTES les données d'un coup (Client + Contrat)
        $validator = Validator::make($request->all(), [
            // Infos Client
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
            
            // Infos Contrat
            'numeroContrat' => 'required|string|unique:contrats',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut',
            'prime' => 'required|numeric',
            'franchise' => 'required|numeric',
            'garantie' => 'required|string',
            'policeAssurance' => 'required|string',
            // Infos Voiture
            'immatriculation' => 'required|string',
            'marque_vehicule' => 'required|string',
            'modele_vehicule' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. On crée le Client (Assuré) avec son mot de passe temporaire
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

        // 3. On crée son contrat d'assurance automobile et on l'attache à ce client !
        $contrat = \App\Models\Contrat::create([
            'numeroContrat' => $request->numeroContrat,
            'typeContrat' => 'Automobile', // On force le type
            'dateDebut' => $request->dateDebut,
            'dateFin' => $request->dateFin,
            'franchise' => $request->franchise,
            'prime' => $request->prime,
            'garantie' => $request->garantie,
            'nomSouscripteur' => $user->nom . ' ' . $user->prenom, // Raccourci métier
            'policeAssurance' => $request->policeAssurance,
            'immatriculation' => $request->immatriculation,
            'marque_vehicule' => $request->marque_vehicule,
            'modele_vehicule' => $request->modele_vehicule,
            'assure_id' => $user->id, // L'ID magique de l'utilisateur qu'on vient juste de créer !
        ]);

        // 4. On retourne le succès total
        return response()->json([
            'message' => 'Compte Assuré ET Contrat auto créés avec succès.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user' => $user,
            'contrat' => $contrat
        ], 201);
    }
    /**
     * Liste tous les utilisateurs pour l'admin
     */
    public function listUsers()
    {
        $users = User::select('id', 'nom', 'prenom', 'email', 'role', 'telephone', 'statut')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Active ou désactive un utilisateur
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->statut = $user->statut === 'Actif' ? 'Inactif' : 'Actif';
        $user->save();

        return response()->json(['message' => 'Statut mis à jour.', 'user' => $user]);
    }

    /**
     * Supprime (archive) un utilisateur
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
