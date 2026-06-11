<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// ROUTES PUBLIQUES (Pas besoin d'être connecté)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-2fa', [AuthController::class, 'verify2fa']);

// ROUTES PROTÉGÉES (L'utilisateur a un token Sanctum valide)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Route spéciale pour changer le mot de passe (Elle ne doit PAS être bloquée par force_password)
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // --- TOUTES LES AUTRES ROUTES SONT BLOQUÉES SI LE MOT DE PASSE DOIT ETRE CHANGÉ ---
    Route::middleware('force_password_change')->group(function () {
        
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        // --- ROUTES ADMIN ---
        Route::middleware('role:Admin')->group(function () {
            Route::post('/admin/professionnal', [UserController::class, 'createProfessional']);
        });
        
        // --- ROUTES ASSUREUR ---
        Route::middleware('role:Assureur')->group(function () {
            Route::post('/assureur/assures', [UserController::class, 'createAssure']);
        });
        
    });
});
