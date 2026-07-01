<?php

namespace App\Http\Controllers;

use App\Models\Sinistre;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SinistreController extends Controller
{
    /**
     * L'Assuré déclare un nouveau sinistre avec pièces jointes
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dateSinistre' => 'required|date',
            'description' => 'required|string',
            'lieuSinistre' => 'required|string',
            'contrat_id' => 'required|integer',
            'documents' => 'nullable|array', // On autorise une liste de documents
            'documents.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120' // Max 5MB par fichier, format photo ou pdf
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // SÉCURITÉ : On vérifie que le contrat existe ET qu'il appartient bien à l'Assuré connecté !
        $contrat = Contrat::where('id', $request->contrat_id)
            ->where('assure_id', auth()->id())
            ->first();

        if (!$contrat) {
            return response()->json(['message' => 'Contrat introuvable ou vous n\'en êtes pas le propriétaire.'], 403);
        }

        // 1. Création du sinistre pur
        $sinistre = Sinistre::create([
            'dateSinistre' => $request->dateSinistre,
            'description' => $request->description,
            'lieuSinistre' => $request->lieuSinistre,
            'statut' => 'En attente', // Par défaut pour un nouveau sinistre
            'assure_id' => auth()->id(),
            'contrat_id' => $request->contrat_id
        ]);

        // 2. Gestion des Fichiers Uploadés (Photos, Constats PDF)
        $fichiersSauvegardes = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                // Laravel stocke automatiquement le fichier dans storage/app/public/sinistres_documents
                $path = $file->store('sinistres_documents', 'public');
                
                $doc = \App\Models\Document::create([
                    'chemin_fichier' => $path,
                    'type_document' => $file->getClientOriginalExtension(), // Ex: "jpg" ou "pdf"
                    'sinistre_id' => $sinistre->id
                ]);

                $fichiersSauvegardes[] = $doc;
            }
        }

        return response()->json([
            'message' => 'Votre déclaration de sinistre a bien été enregistrée et est en attente de traitement.',
            'sinistre' => $sinistre,
            'fichiers_joints' => $fichiersSauvegardes
        ], 201);
    }
}
