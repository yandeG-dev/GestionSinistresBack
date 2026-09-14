<?php

namespace App\Http\Controllers;

use App\Models\Sinistre;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SinistreController extends Controller
{
    /**
     * L'AssurÃ© dÃ©clare un nouveau sinistre avec piÃ¨ces jointes
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'typeSinistre' => 'required|string',
            'dateSinistre' => 'required|date',
            'heureSinistre' => 'nullable|date_format:H:i',
            'description' => 'required|string',
            'lieuSinistre' => 'required|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $numeroDossier = 'SIN-' . date('Y') . '-' . rand(1000, 9999);

        // 1. CrÃ©ation du sinistre pur
        $sinistre = Sinistre::create([
            'numeroDossier' => $numeroDossier,
            'typeSinistre' => $request->typeSinistre,
            'heureSinistre' => $request->heureSinistre,
            'dateSinistre' => $request->dateSinistre,
            'description' => $request->description,
            'lieuSinistre' => $request->lieuSinistre,
            'statut' => 'En attente',
            'assure_id' => auth()->id(),
            'contrat_id' => auth()->user()->contrats()->latest()->first()?->id
        ]);

        // 2. Gestion des Fichiers UploadÃ©s (Photos, Constats PDF)
        $fichiersSauvegardes$sinistre->load('contrat');
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
            'message' => 'Votre dÃ©claration de sinistre a bien Ã©tÃ© enregistrÃ©e et est en attente de traitement.',
            'sinistre' => $sinistre,
            'fichiers_joints' => $fichiersSauvegardes
        ], 201);
    }

    public function mesSinistres()
{
    // On rÃ©cupÃ¨re l'utilisateur connectÃ©
    $assure = Auth::user();

    // On charge ses sinistres avec les relations nÃ©cessaires
    $sinistres = Sinistre::with(['contrat', 'documents'])
        ->where('assure_id', $assure->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($sinistres);
}
// Cette mÃ©thode permet Ã  un gestionnaire de voir tous les sinistres de ses assurÃ©s
public function showDetailsSinistre($id)
{
    $sinistre = Sinistre::with(['contrat.vehicule', 'documents'])
        ->where('id', $id)
        ->where(function ($query) {
            $query->where('assure_id', Auth::id())
                  ->orWhere('gestionnaire_id', Auth::id());
        })
        ->first();

    if (!$sinistre) {
        return response()->json(['message' => 'Sinistre introuvable.'], 404);
    }

    return response()->json($sinistre);
}
// Cette mÃ©thode permet Ã  un gestionnaire de voir tous les sinistres de ses assurÃ©s
public function showAllSinitresByGestionnaire()
{
    $sinistres = Sinistre::with(['contrat.vehicule', 'documents', 'assure'])
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($sinistres);
}

/**
 * Archiver un sinistre (le gestionnaire ou l'admin peut archiver)
 * Le sinistre n'est pas supprimÃ©, il passe en statut "ArchivÃ©" et est soft-deleted
 */
public function archiverSinistre($id)
{
    $sinistre = Sinistre::where('id', $id)
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->first();

    if (!$sinistre) {
        return response()->json(['message' => 'Sinistre introuvable ou non autorisÃ©.'], 404);
    }

    // On vÃ©rifie qu'il peut Ãªtre archivÃ© (pas en cours de traitement actif)
    if ($sinistre->statut === 'En cours') {
        return response()->json(['message' => 'Impossible d\'archiver un sinistre en cours de traitement.'], 422);
    }

    $sinistre->statut = 'ArchivÃ©';
    $sinistre->save();
    $sinistre->delete(); // SoftDelete : enregistre deleted_at, invisible des requÃªtes normales

    return response()->json(['message' => 'Sinistre archivÃ© avec succÃ¨s.', 'sinistre' => $sinistre]);
}

/**
 * DÃ©sarchiver un sinistre (restauration)
 */
public function desarchiverSinistre($id)
{
    $sinistre = Sinistre::onlyTrashed()
        ->where('id', $id)
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->first();

    if (!$sinistre) {
        return response()->json(['message' => 'Sinistre archivÃ© introuvable ou non autorisÃ©.'], 404);
    }

    $sinistre->restore(); // Restaure le soft delete
    $sinistre->statut = 'En attente'; // Repasse en attente aprÃ¨s restauration
    $sinistre->save();

    return response()->json(['message' => 'Sinistre dÃ©sarchivÃ© avec succÃ¨s.', 'sinistre' => $sinistre]);
}

/**
 * Voir tous les sinistres archivÃ©s (gestionnaire)
 */
public function sinistresArchives()
{
    $sinistres = Sinistre::onlyTrashed()
        ->with(['contrat.vehicule', 'documents', 'assure'])
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->orderBy('deleted_at', 'desc')
        ->get();

    return response()->json($sinistres);
}

}



