<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
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
            $auth = (new Factory)
                ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
                ->createAuth();
        
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub'); // UID do usuário
        
            // Para pegar o e-mail:
            $email = $verifiedIdToken->claims()->get('email');
        
            // Se quiser pegar todos os claims:
            // $claims = $verifiedIdToken->claims()->all();
        
            // Adicione ao request se quiser
            $request->attributes->add([
                'firebase_uid' => $firebaseUid,
                'firebase_email' => $email,
            ]);
        
        } catch (InvalidToken $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(['error' => 'Erro ao validar token'], 401);
        }

        return $next($request);
    }
}