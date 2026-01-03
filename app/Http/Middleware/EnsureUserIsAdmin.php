<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Agora o $request->user() retornará o objeto User preenchido
        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'error' => 'Acesso negado. Apenas administradores.',
            'debug_info' => [
                'identificado' => $user ? 'Sim' : 'Não',
                'role_atual' => $user ? $user->role : 'n/a'
            ]
        ], 403);
    }
}