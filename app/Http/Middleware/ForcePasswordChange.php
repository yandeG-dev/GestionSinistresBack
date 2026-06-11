<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est connecté mais qu'il doit changer son mot de passe
        if (auth()->check() && auth()->user()->doit_changer_mdp) {
            return response()->json([
                'message' => 'Action bloquée. Vous devez impérativement changer votre mot de passe temporaire.',
                'require_password_change' => true
            ], 403);
        }

        return $next($request);
    }
}
