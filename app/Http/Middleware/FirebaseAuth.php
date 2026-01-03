<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;

class FirebaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken();
        
        if (!$idToken) {
            $authorizationHeader = $request->header('Authorization') ?: $request->server('HTTP_AUTHORIZATION');
            
            if ($authorizationHeader && preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
                $idToken = $matches[1];
            }
        }
    
        if (!$idToken) {
            // Log para debug no painel da Vercel se continuar falhando
            \Log::error('Cabeçalhos recebidos na Vercel:', $request->headers->all());
            return response()->json(['error' => 'Token não fornecido'], 401);
        }
    
        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            $auth = $factory->createAuth();
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            
            $request->attributes->add(['firebase_uid' => $firebaseUid]);
            
            $user = \App\Models\User::where('firebase_uid', $firebaseUid)->first();
            
            if (!$user) {
                // Se for a rota de sincronização, permite passar para o Controller criar o registro
                if ($request->is('*users/sync-on-register')) {
                    return $next($request);
                }
                
                return response()->json(['error' => 'Usuário não sincronizado'], 401);
            }
        
            \Illuminate\Support\Facades\Auth::login($user);
            return $next($request);

        } catch (\Throwable $e) {
                return response()->json([
            'error' => 'Falha na autenticação',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString() 
        ], 401);
            }
        }
}