<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Vehicule;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    /**
     * Créer un nouveau contrat, avec son véhicule associé.
     */
    public function createContrat(Request $request)
    {
        $request->validate([
            // Contrat
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'franchise' => 'required|numeric',
            'prime' => 'required|numeric',
            'garantie' => 'required|string|max:255',
            'nom_souscripteur' => 'required|string|max:255',
            'police_assurance' => 'required|string|max:50',
            'assure_id' => 'required|exists:users,id',
            // Véhicule (créé en même temps que le contrat)
            'marque' => 'required|string|max:50',
            'modele' => 'required|string|max:50',
            'immatriculation' => 'required|string|unique:vehicules,immatriculation',
            'type_carburant' => 'required|string|max:20',
            'puissance_fiscale' => 'required|integer|min:1',
            'date_mise_en_circulation' => 'required|date',
        ]);

        // 1. Créer le véhicule
        $vehicule = Vehicule::create([
            'marque' => $request->marque,
            'modele' => $request->modele,
            'immatriculation' => $request->immatriculation,
            'type_carburant' => $request->type_carburant,
            'puissance_fiscale' => $request->puissance_fiscale,
            'date_mise_en_circulation' => $request->date_mise_en_circulation,
        ]);

        // 2. Créer le contrat, lié au véhicule
        $contrat = Contrat::create([
            'numeroContrat' => $this->genererNumeroContrat(),
            'typeContrat' => 'TOUT RISQUE',
            'dateDebut' => $request->date_debut,
            'dateFin' => $request->date_fin,
            'franchise' => $request->franchise,
            'prime' => $request->prime,
            'garantie' => $request->garantie,
            'nomSouscripteur' => $request->nom_souscripteur,
            'policeAssurance' => $request->police_assurance,
            'assure_id' => $request->assure_id,
            'vehicule_id' => $vehicule->id
        ]);

        return response()->json([
            'message' => 'Contrat créé avec succès',
            'contrat' => $contrat,
            'vehicule' => $vehicule
        ], 201);
    }

    /**
     * Voir un contrat : accessible par l'assuré propriétaire ou son gestionnaire.
     */
    public function showContrat(Contrat $contrat)
    {
        $estProprietaire = $contrat->assure_id === auth()->id();
        $estGestionnaireConcerne = $contrat->assure && $contrat->assure->gestionnaire_id === auth()->id();

        if (!$estProprietaire && !$estGestionnaireConcerne) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir ce contrat.'], 403);
        }

        return response()->json($contrat->load(['vehicule']));
    }

    /**
     * Modifier un contrat et/ou son véhicule associé.
     * Réservé au gestionnaire concerné.
     */
    public function updateContrat(Request $request, Contrat $contrat)
    {
        $estGestionnaireConcerne = $contrat->assure && $contrat->assure->gestionnaire_id === auth()->id();

        if (!$estGestionnaireConcerne) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier ce contrat.'], 403);
        }

        $request->validate([
            // Contrat
            'date_debut' => 'sometimes|date',
            'date_fin' => 'sometimes|date|after:date_debut',
            'franchise' => 'sometimes|numeric',
            'prime' => 'sometimes|numeric',
            'garantie' => 'sometimes|string|max:255',
            'nom_souscripteur' => 'sometimes|string|max:255',
            'police_assurance' => 'sometimes|string|max:50',
            // Véhicule
            'marque' => 'sometimes|string|max:50',
            'modele' => 'sometimes|string|max:50',
            'immatriculation' => 'sometimes|string|unique:vehicules,immatriculation,' . $contrat->vehicule_id,
            'type_carburant' => 'sometimes|string|max:20',
            'puissance_fiscale' => 'sometimes|integer|min:1',
            'date_mise_en_circulation' => 'sometimes|date',
        ]);

        // Mise à jour des champs du contrat, uniquement ceux envoyés
        $contrat->update($request->only([
            'date_debut', 'date_fin', 'franchise', 'prime',
            'garantie', 'nom_souscripteur', 'police_assurance'
        ]));

        // Mise à jour du véhicule, seulement si des champs véhicule ont été envoyés
        if ($request->hasAny([
            'marque', 'modele', 'immatriculation',
            'type_carburant', 'puissance_fiscale', 'date_mise_en_circulation'
        ])) {
            $contrat->vehicule->update($request->only([
                'marque', 'modele', 'immatriculation',
                'type_carburant', 'puissance_fiscale', 'date_mise_en_circulation'
            ]));
        }

        return response()->json([
            'message' => 'Contrat modifié avec succès',
            'contrat' => $contrat->load('vehicule')
        ]);
    }

    /**
     * Archiver un contrat (soft delete — remplace la suppression physique).
     * Le contrat n'est pas effacé, il est masqué des requêtes normales.
     * Réservé au gestionnaire concerné.
     */
    public function archiverContrat(Contrat $contrat)
    {
        $estGestionnaireConcerne = $contrat->assure && $contrat->assure->gestionnaire_id === auth()->id();

        if (!$estGestionnaireConcerne) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à archiver ce contrat.'], 403);
        }

        $contrat->delete(); // SoftDelete : enregistre deleted_at, invisible des requêtes normales

        return response()->json([
            'message' => 'Contrat archivé avec succès. Il reste consultable dans les archives.'
        ]);
    }

    /**
     * Voir tous les contrats archivés.
     * Réservé au gestionnaire.
     */
    public function contratsArchives()
    {
        $contrats = Contrat::onlyTrashed()
            ->with('vehicule', 'assure')
            ->whereHas('assure', function ($query) {
                $query->where('gestionnaire_id', auth()->id());
            })
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($contrats);
    }

    /**
     * Génère un numéro de contrat unique.
     * À adapter selon ton format métier (ex: préfixe, année, séquence...).
     */
    private function genererNumeroContrat(): string
    {
        return 'CTR-' . now()->format('Y') . '-' . strtoupper(uniqid());
    }
}