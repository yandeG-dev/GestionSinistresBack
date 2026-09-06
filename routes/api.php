<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\SinistreController;

// ROUTES PUBLIQUES (Pas besoin d'être connecté)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-2fa', [AuthController::class, 'verify2fa']);

// ROUTES PROTÉGÉES (L'utilisateur a un token Sanctum valide)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Route spéciale pour changer le mot de passe (Elle ne doit PAS être bloquée par force_password)
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    
    // Activer/Désactiver le 2FA
    Route::patch('/auth/toggle-2fa', [AuthController::class, 'toggle2FA']);

    // --- TOUTES LES AUTRES ROUTES SONT BLOQUÉES SI LE MOT DE PASSE DOIT ETRE CHANGÉ ---
    Route::middleware('force_password_change')->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // --- ROUTES ADMIN ---
        Route::middleware('role:Admin')->group(function () {
            Route::post('/admin/professionnal', [UserController::class, 'createProfessional']);
            Route::get('/admin/users', [UserController::class, 'listUsers']);
            Route::patch('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
            Route::delete('/admin/users/{id}', [UserController::class, 'deleteUser']);
        });

        // --- ROUTES GESTIONNAIRE ---
        Route::middleware('role:Gestionnaire')->group(function () {
            // Assurés
            Route::post('/gestionnaire/assures', [UserController::class, 'createAssure']);

            // Sinistres
            Route::get('/gestionnaire/sinistres', [SinistreController::class, 'showAllSinitresByGestionnaire']);
            Route::get('/gestionnaire/sinistres/{id}', [SinistreController::class, 'showDetailsSinistre']);

            // Contrats (CRUD complet implémenté dans ContratController)
            Route::post('/contrats', [ContratController::class, 'createContrat']);
            Route::get('/contrats', [ContratController::class, 'contratsArchives'])->name('contrats.archives'); // archives seulement
            Route::get('/contrats/{contrat}', [ContratController::class, 'showContrat']);
            Route::put('/contrats/{contrat}', [ContratController::class, 'updateContrat']);
            Route::patch('/contrats/{contrat}/archiver', [ContratController::class, 'archiverContrat']);

            // Sinistres — archivage
            Route::patch('/gestionnaire/sinistres/{id}/archiver', [SinistreController::class, 'archiverSinistre']);
            Route::patch('/gestionnaire/sinistres/{id}/desarchiver', [SinistreController::class, 'desarchiverSinistre']);
            Route::get('/gestionnaire/sinistres/archives', [SinistreController::class, 'sinistresArchives']);
        });

        // --- ROUTES ASSURE (Client) ---
        Route::middleware('role:Assure')->group(function () {
            // Sinistres
            Route::post('/sinistres', [SinistreController::class, 'store']);
            Route::get('/sinistres', [SinistreController::class, 'mesSinistres']);
            Route::get('/sinistres/{id}', [SinistreController::class, 'showDetailsSinistre']);

            // Voir son contrat
            Route::get('/contrats/{contrat}', [ContratController::class, 'showContrat']);
        });

    });
});
