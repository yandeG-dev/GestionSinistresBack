<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. On vérifie si l'utilisateur est connecté
        if (!auth()->check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // 2. On vérifie s'il a au moins un des rôles autorisés
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Accès refusé. Réservé aux profils : ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}
