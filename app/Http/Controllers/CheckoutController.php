<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AbacatePayService;
use App\Models\User; // Ajuste conforme seu modelo de usuário

class CheckoutController extends Controller
{
    protected $abacateService;

    public function __construct(AbacatePayService $abacateService)
    {
        $this->abacateService = $abacateService;
    }

    // public function createAbacateCheckout(Request $request)
    // {
    //     try {
    //         // Se você usa autenticação padrão do Laravel:
    //         // $user = $request->user(); 
            
    //         // Se você envia o ID manualmente do Vue (como no seu código):
    //         $user = User::where('firebase_uid', $request->user_id)->first();

    //         if (!$user) {
    //             return response()->json(['error' => 'Usuário não encontrado'], 404);
    //         }

    //         // VALIDAÇÃO CRÍTICA: O AbacatePay exige CPF
    //         if (empty($user->cpf)) {
    //             return response()->json([
    //                 'error' => 'Por favor, atualize seu perfil com seu CPF antes de prosseguir.'
    //             ], 400);
    //         }

    //         // Valor em centavos: R$ 19,90 = 1990
    //         $result = $this->abacateService->createBilling(
    //             [
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'cpf' => $user->cpf // Remove pontos e traços se necessário
    //             ],
    //             1990 
    //         );

    //         // Retorna a URL para o Vue redirecionar
    //         return response()->json([
    //             'payment_url' => $result['data']['url'] 
    //         ]);

    //     } catch (\Exception $e) {
    //         // Log o erro para você debuggar
    //         \Log::error('Erro Checkout AbacatePay: ' . $e->getMessage());
            
    //         return response()->json([
    //             'error' => 'Erro ao comunicar com gateway de pagamento.'
    //         ], 500);
    //     }
    // }

    public function createAbacateCheckout(Request $request)
    {
        try {
            // 1. Busca o usuário pelo ID do Firebase/Clerk
            $user = User::where('firebase_uid', $request->user_id)->first();

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            // 2. NOVO: Se o Vue enviou um CPF na requisição, salvamos no banco
            if ($request->has('cpf') && !empty($request->cpf)) {
                // Remove caracteres não numéricos para salvar limpo
                $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);
                $user->cpf = $cpfLimpo;
                $user->save();
            }

            // 3. Validação: Se mesmo assim não tiver CPF, retorna erro 400
            if (empty($user->cpf)) {
                // Retornamos um código específico 'MISSING_CPF' para o Vue saber o que fazer
                return response()->json([
                    'error' => 'Por favor, atualize seu perfil com seu CPF.',
                    'code'  => 'MISSING_CPF' 
                ], 400);
            }

            // 4. Cria a cobrança no AbacatePay
            $result = $this->abacateService->createBilling(
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'cpf' => $user->cpf,
                    'phone' => $user->phone
                ],
                1990 // Valor em centavos
            );

            return response()->json([
                'payment_url' => $result['data']['url'] 
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro Checkout: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
}