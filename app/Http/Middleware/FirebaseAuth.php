<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Auth; // Importante para autenticar no Laravel
use App\Models\User; // Importante para buscar o usuário no banco

class FirebaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken();
    
        if (!$idToken) {
            return response()->json(['error' => 'Token não fornecido'], 401);
        }
    
        try {
            // Certifique-se que o path em config/firebase.php aponta para o lugar certo em produção
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            $auth = $factory->createAuth();
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
        
            $user = \App\Models\User::where('firebase_uid', $firebaseUid)->first();
        
            if (!$user) {
                // Em produção, se o usuário não existe no banco local, ele não está autorizado
                return response()->json(['error' => 'Usuário não sincronizado no banco de dados'], 401);
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