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

    public function mesSinistres()
{
    // On récupère l'utilisateur connecté
    $assure = Auth::user();

    // On charge ses sinistres avec les relations nécessaires
    $sinistres = Sinistre::with(['contrat', 'documents'])
        ->where('assure_id', $assure->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($sinistres);
}
// Cette méthode permet à un gestionnaire de voir tous les sinistres de ses assurés
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
// Cette méthode permet à un gestionnaire de voir tous les sinistres de ses assurés
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
 * Le sinistre n'est pas supprimé, il passe en statut "Archivé" et est soft-deleted
 */
public function archiverSinistre($id)
{
    $sinistre = Sinistre::where('id', $id)
        ->whereHas('assure', function ($query) {
            $query->where('gestionnaire_id', Auth::id());
        })
        ->first();

    if (!$sinistre) {
        return response()->json(['message' => 'Sinistre introuvable ou non autorisé.'], 404);
    }

    // On vérifie qu'il peut être archivé (pas en cours de traitement actif)
    if ($sinistre->statut === 'En cours') {
        return response()->json(['message' => 'Impossible d\'archiver un sinistre en cours de traitement.'], 422);
    }

    $sinistre->statut = 'Archivé';
    $sinistre->save();
    $sinistre->delete(); // SoftDelete : enregistre deleted_at, invisible des requêtes normales

    return response()->json(['message' => 'Sinistre archivé avec succès.', 'sinistre' => $sinistre]);
}

/**
 * Désarchiver un sinistre (restauration)
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
        return response()->json(['message' => 'Sinistre archivé introuvable ou non autorisé.'], 404);
    }

    $sinistre->restore(); // Restaure le soft delete
    $sinistre->statut = 'En attente'; // Repasse en attente après restauration
    $sinistre->save();

    return response()->json(['message' => 'Sinistre désarchivé avec succès.', 'sinistre' => $sinistre]);
}

/**
 * Voir tous les sinistres archivés (gestionnaire)
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
