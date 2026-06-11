<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// ROUTES PUBLIQUES (Pas besoin d'être connecté)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-2fa', [AuthController::class, 'verify2fa']);

// ROUTES PROTÉGÉES (L'utilisateur a déjà donné un token Sanctum valide)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // --- ROUTES ADMIN ---
    Route::middleware('role:Admin')->group(function () {
        Route::post('/admin/professionnal', [UserController::class, 'createProfessional']);
    });
    
    // --- ROUTES ASSUREUR ---
    Route::middleware('role:Assureur')->group(function () {
        Route::post('/assureur/assures', [UserController::class, 'createAssure']);
    });
});
