<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Auth\Token\Exception\InvalidToken;

class FirebaseAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken();

        if(!$idToken){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            
            $auth = $factory->createAuth();
        
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub'); // UID do usuário
        
            $email = $verifiedIdToken->claims()->get('email');
        
            $request->attributes->add([
                'firebase_uid' => $firebaseUid,
                'firebase_email' => $email,
            ]);
        
        } catch (InvalidToken $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(['error' => 'Erro ao validar token', 'details' => $e->getMessage()], 500);
        }

        return $next($request);
    }
}