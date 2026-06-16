<?php

namespace App\Http\Controllers;

use App\Models\Sinistre;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SinistreController extends Controller
{
    /**
     * L'Assuré déclare un nouveau sinistre
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dateSinistre' => 'required|date',
            'description' => 'required|string',
            'lieuSinistre' => 'required|string',
            'contrat_id' => 'required|integer',
            'donnees_specifiques' => 'nullable|array' // Le format JSON
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

        // Création du sinistre
        $sinistre = Sinistre::create([
            'dateSinistre' => $request->dateSinistre,
            'description' => $request->description,
            'lieuSinistre' => $request->lieuSinistre,
            'statut' => 'En attente', // Par défaut pour un nouveau sinistre
            'donnees_specifiques' => $request->donnees_specifiques,
            'assure_id' => auth()->id(),
            'contrat_id' => $request->contrat_id
        ]);

        return response()->json([
            'message' => 'Votre déclaration de sinistre a bien été enregistrée et est en attente de traitement.',
            'sinistre' => $sinistre
        ], 201);
    }
}
