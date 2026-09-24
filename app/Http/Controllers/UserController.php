<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\UserCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function createProfessional(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'role'      => 'required|in:Administrateur,Agent,Expert,Gestionnaire,Comptable',
            'telephone' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $motDePasseAleatoire = Str::random(10);
        $user = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'password'  => Hash::make($motDePasseAleatoire),
            'role'      => $request->role,
            'telephone' => $request->telephone,
            'adresse'   => 'Non renseignee',
            'doit_changer_mdp' => true,
        ]);
        
        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $motDePasseAleatoire));
        } catch (\Exception $e) {
            \Log::error("Erreur d'envoi d'email : " . $e->getMessage());
        }
        
        return response()->json([
            'message'                 => 'Le profil professionel cree avec succes.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user'                    => $user,
        ], 201);
    }

    public function createAssure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'             => 'required|string|max:255',
            'prenom'          => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'telephone'       => 'required|string|max:20',
            'adresse'         => 'required|string',
            'numeroContrat'   => 'required|string|unique:contrats',
            'dateDebut'       => 'required|date',
            'dateFin'         => 'required|date|after:dateDebut',
            'prime'           => 'required|numeric',
            'franchise'       => 'required|numeric',
            'garantie'        => 'required|string',
            'policeAssurance' => 'required|string',
            'immatriculation' => 'required|string',
            'marque_vehicule' => 'required|string',
            'modele_vehicule' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $motDePasseAleatoire = Str::random(10);
        $user = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'password'  => Hash::make($motDePasseAleatoire),
            'role'      => 'Assure',
            'telephone' => $request->telephone,
            'adresse'   => $request->adresse,
            'gestionnaire_id' => Auth::id(),
            'doit_changer_mdp' => true,
        ]);

        $vehicule = \App\Models\Vehicule::create([
            'marque'          => $request->marque_vehicule,
            'modele'          => $request->modele_vehicule,
            'immatriculation' => $request->immatriculation,
        ]);

        $contrat = \App\Models\Contrat::create([
            'numeroContrat'   => $request->numeroContrat,
            'typeContrat'     => 'Automobile',
            'dateDebut'       => $request->dateDebut,
            'dateFin'         => $request->dateFin,
            'franchise'       => $request->franchise,
            'prime'           => $request->prime,
            'garantie'        => $request->garantie,
            'policeAssurance' => $request->policeAssurance,
            'assure_id'       => $user->id,
            'vehicule_id'     => $vehicule->id,
        ]);

        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $motDePasseAleatoire));
        } catch (\Exception $e) {
            \Log::error("Erreur d'envoi d'email pour l'assure : " . $e->getMessage());
        }

        return response()->json([
            'message'                 => 'Compte Assure et Contrat crees avec succes.',
            'mot_de_passe_temporaire' => $motDePasseAleatoire,
            'user'                    => $user,
            'contrat'                 => $contrat,
            'vehicule'                => $vehicule,
        ], 201);
    }

    public function listAssures()
    {
        $users = User::where('role', 'Assure')
            ->where('gestionnaire_id', Auth::id())
            ->select('id', 'nom', 'prenom', 'email', 'role', 'telephone', 'statut')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($users);
    }

    public function listUsers()
    {
        $users = User::select('id', 'nom', 'prenom', 'email', 'role', 'telephone', 'statut')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($users);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->statut = $user->statut === 'Actif' ? 'Inactif' : 'Actif';
        $user->save();
        return response()->json(['message' => 'Statut mis a jour.', 'user' => $user]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprime.']);
    }
}