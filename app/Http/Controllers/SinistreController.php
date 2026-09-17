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
     * L'AssurÃƒÂ© dÃƒÂ©clare un nouveau sinistre avec piÃƒÂ¨ces jointes
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

        // 1. CrÃƒÂ©ation du sinistre pur
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

        // 2. Gestion des Fichiers UploadÃƒÂ©s (Photos, Constats PDF)
        $sinistre->load('contrat');
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
            'message' => 'Votre dÃƒÂ©claration de sinistre a bien ÃƒÂ©tÃƒÂ© enregistrÃƒÂ©e et est en attente de traitement.',
            'sinistre' => $sinistre,
            'fichiers_joints' => $fichiersSauvegardes
        ], 201);
    }

    public function mesSinistres()
{
    // On rÃƒÂ©cupÃƒÂ¨re l'utilisateur connectÃƒÂ©
    $assure = Auth::user();

    // On charge ses sinistres avec les relations nÃƒÂ©cessaires
    $sinistres = Sinistre::with(['contrat', 'documents'])
        ->where('assure_id', $assure->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($sinistres);
}
// Cette mÃƒÂ©thode permet ÃƒÂ  un gestionnaire de voir tous les sinistres de ses assurÃƒÂ©s
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
// Cette mÃƒÂ©thode permet ÃƒÂ  un gestionnaire de voir tous les sinistres de ses assurÃƒÂ©s
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
 * Le sinistre n'est pas supprimÃƒÂ©, il passe en statut "ArchivÃƒÂ©" et est soft-deleted
 */
public function archiverSinistre($id)
{
    $sinistre = Sinistre::where('id', $id)
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->first();

    if (!$sinistre) {
        return response()->json(['message' => 'Sinistre introuvable ou non autorisÃƒÂ©.'], 404);
    }

    // On vÃƒÂ©rifie qu'il peut ÃƒÂªtre archivÃƒÂ© (pas en cours de traitement actif)
    if ($sinistre->statut === 'En cours') {
        return response()->json(['message' => 'Impossible d\'archiver un sinistre en cours de traitement.'], 422);
    }

    $sinistre->statut = 'ArchivÃƒÂ©';
    $sinistre->save();
    $sinistre->delete(); // SoftDelete : enregistre deleted_at, invisible des requÃƒÂªtes normales

    return response()->json(['message' => 'Sinistre archivÃƒÂ© avec succÃƒÂ¨s.', 'sinistre' => $sinistre]);
}

/**
 * DÃƒÂ©sarchiver un sinistre (restauration)
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
        return response()->json(['message' => 'Sinistre archivÃƒÂ© introuvable ou non autorisÃƒÂ©.'], 404);
    }

    $sinistre->restore(); // Restaure le soft delete
    $sinistre->statut = 'En attente'; // Repasse en attente aprÃƒÂ¨s restauration
    $sinistre->save();

    return response()->json(['message' => 'Sinistre dÃƒÂ©sarchivÃƒÂ© avec succÃƒÂ¨s.', 'sinistre' => $sinistre]);
}

/**
 * Voir tous les sinistres archivÃƒÂ©s (gestionnaire)
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



