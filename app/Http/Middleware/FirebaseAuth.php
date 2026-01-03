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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            $auth = $factory->createAuth();
        
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            // BUSCA O USUÁRIO NO BANCO DE DADOS LOCAL
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado no sistema local.'], 404);
            }

            // AUTENTICA O USUÁRIO NO LARAVEL
            Auth::login($user);

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