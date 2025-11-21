<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleAbacateHook(Request $request)
    {
        try {
            // Mantive o log para garantia
            Log::info('WEBHOOK PROCESSANDO...');

            $event = $request->input('event');
            $data = $request->input('data');

            if ($event === 'billing.paid') {

                // --- CORREÇÃO AQUI ---
                // Baseado no seu log: data -> billing -> customer -> metadata -> email
                $billing = $data['billing'] ?? [];
                $customer = $billing['customer'] ?? [];
                $metadata = $customer['metadata'] ?? [];

                // Tenta pegar do metadata (onde veio no seu log)
                $customerEmail = $metadata['email'] ?? null;
                $customerCpf   = $metadata['taxId'] ?? null;

                // Fallback: Se não estiver no metadata, tenta direto no customer (alguns gateways variam)
                if (!$customerEmail) {
                    $customerEmail = $customer['email'] ?? null;
                }

                Log::info("Dados extraídos: Email: $customerEmail | CPF: $customerCpf");

                if (!$customerEmail && !$customerCpf) {
                    Log::error('Webhook falhou: Email/CPF não encontrados na estrutura (metadata).');
                    return response()->json(['status' => 'ignored_missing_data']); 
                }
                // ---------------------

                // Busca o usuário
                $user = User::where('email', $customerEmail)->first();
                
                // Tenta pelo CPF se não achou pelo email
                if (!$user && $customerCpf) {
                     $cpfLimpo = preg_replace('/[^0-9]/', '', $customerCpf);
                     $user = User::where('cpf', $cpfLimpo)->first();
                }

                if ($user) {
                    $this->activatePremium($user);
                    Log::info("SUCESSO FINAL: Usuário {$user->email} (ID: {$user->id}) virou Premium.");
                } else {
                    Log::warning("USUÁRIO NÃO ENCONTRADO NO BANCO: $customerEmail");
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('ERRO FATAL NO WEBHOOK: ' . $e->getMessage());
            return response()->json(['status' => 'error_handled']);
        }
    }

    private function activatePremium(User $user)
    {
        $now = Carbon::now();
        
        // Se já é premium e vence no futuro, soma na data de vencimento
        if ($user->is_premium && $user->premium_expires_at && Carbon::parse($user->premium_expires_at)->isFuture()) {
            $newExpiration = Carbon::parse($user->premium_expires_at)->addDays(30);
        } else {
            // Se não, começa a contar de agora
            $newExpiration = $now->addDays(30);
        }

        $user->update([
            'is_premium' => 'true',
            'premium_expires_at' => $newExpiration,
        ]);
    }
}