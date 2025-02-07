<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Subscription;
use Illuminate\Support\Facades\Log;


class PaymentController extends Controller {
    /**
     * Cria uma sessão de checkout para assinatura premium.
     */
    public function createCheckoutSession(Request $request) {
        $clerkUserId = $request->input('clerk_user_id');
        if (!$clerkUserId) {
            return response()->json(['error' => 'clerk_user_id is required'], 400);
        }

        // Define a API key do Stripe usando config('stripe.sk') – certifique-se de que ela está definida
        // Stripe::setApiKey(config('stripe.sk'));
        Stripe::setApiKey(env('STRIPE_SK'));

        $successUrl = env('FRONTEND_SUCCESS_URL');
        $cancelUrl  = env('FRONTEND_CANCEL_URL');

        // Logs para confirmar os valores
        \Log::info('FRONTEND_SUCCESS_URL: ' . $successUrl);
        \Log::info('FRONTEND_CANCEL_URL: ' . $cancelUrl);

        // Crie a sessão de checkout
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'brl',
                    'product_data' => [
                        'name' => 'Plano Premium',
                    ],
                    'unit_amount' => 1990, // R$19,90 em centavos
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => [
                'metadata' => [
                    'clerk_user_id' => $clerkUserId,
                ],
            ],
            'metadata' => [
                'clerk_user_id' => $clerkUserId,
            ],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
        ]);

        return response()->json(['sessionId' => $session->id]);
    }


    /**
     * Endpoint para confirmar a assinatura (pode ser chamado via redirecionamento ou webhook).
     */
    public function confirmSubscription(Request $request)
    {
        // Obter o payload bruto e a assinatura do header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        // Verificar a assinatura do webhook
        try {
            Stripe::setApiKey(env('STRIPE_SK'));
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid webhook signature: ' . $e->getMessage()], 403);
        }

        // Processa o evento "checkout.session.completed"
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Verifica se o pagamento foi concluído com sucesso
            if ($session->payment_status !== 'paid') {
                return response()->json(['error' => 'Payment not completed'], 400);
            }

            // Tenta obter o clerk_user_id dos metadados da assinatura
            $clerkUserId = null;
            if (isset($session->subscription)) {
                $subscription = Subscription::retrieve($session->subscription);
                if (isset($subscription->metadata) && !empty($subscription->metadata->clerk_user_id)) {
                    $clerkUserId = $subscription->metadata->clerk_user_id;
                }
            }

            $updated = false;
            if ($clerkUserId) {
                $updated = $this->updateClerkUserById($clerkUserId);
            } else {
                // Fallback: usar o email do cliente
                $customerEmail = $session->customer_details->email;
                $updated = $this->updateClerkUserByEmail($customerEmail);
            }

            if (!$updated) {
                return response()->json(['error' => 'Failed to update user on Clerk'], 500);
            }

            return response()->json(['message' => 'Subscription confirmed via webhook']);
        }

        // Se o evento não for do tipo que queremos tratar, retorne sucesso para evitar reenvio
        return response()->json(['message' => 'Event type not handled'], 200);
    }

    /**
     * Atualiza o usuário no Clerk utilizando o clerk_user_id.
     */
    private function updateClerkUserById($clerkUserId)
    {
        $clerkApiKey = env('CLERK_API_KEY');
        $endpoint = "https://api.clerk.dev/v1/users/{$clerkUserId}";

        $updateResponse = Http::withHeaders([
            'Authorization' => "Bearer {$clerkApiKey}",
            'Content-Type'  => 'application/json',
        ])->patch($endpoint, [
            'public_metadata' => ['subscriptionPlan' => 'premium'],
            'private_metadata' => [
                // Aqui você pode armazenar outros dados, como:
                // 'stripeCustomerId' => $session->customer, // se disponível
                // 'stripeSubscriptionId' => $subscription->id, // se disponível
            ],
        ]);

        Log::info('Clerk update response by ID:', [
            'status' => $updateResponse->status(),
            'body'   => $updateResponse->body()
        ]);

        return $updateResponse->successful();
    }

    /**
     * Atualiza o usuário no Clerk utilizando o email (fallback).
     */
    private function updateClerkUserByEmail($email)
    {
        $clerkApiKey = env('CLERK_API_KEY');
        $endpoint = "https://api.clerk.dev/v1/users";

        // Constrói a URL com o parâmetro de email
        $url = $endpoint . "?email_address=" . urlencode($email);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$clerkApiKey}",
            'Content-Type'  => 'application/json',
        ])->get($url);

        Log::info('Clerk GET response by email:', $response->json());

        $userData = $response->json();
        $users = isset($userData['data']) ? $userData['data'] : $userData;

        if (!empty($users)) {
            if (isset($users[0]['id'])) {
                $userId = $users[0]['id'];
            } elseif (isset($users['id'])) {
                $userId = $users['id'];
            } else {
                Log::error('User not found in Clerk', ['response' => $userData]);
                return false;
            }

            $updateResponse = Http::withHeaders([
                'Authorization' => "Bearer {$clerkApiKey}",
                'Content-Type'  => 'application/json',
            ])->patch("$endpoint/$userId", [
                'public_metadata' => ['subscriptionPlan' => 'premium']
            ]);

            Log::info('Clerk update response by email:', $updateResponse->json());

            return $updateResponse->successful();
        } else {
            Log::error('No data returned from Clerk for email: ' . $email);
        }
        return false;
    }

    /**
     * (Opcional) Endpoint para processar webhooks do Stripe.
     */
    public function handleWebhook(Request $request) {
        \Log::info('Headers recebidos:', $request->headers->all());
        \Log::info('Payload recebido:', ['payload' => $request->getContent()]);
    
        // $payload = $request->getContent();
        $payload = file_get_contents('php://input');
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');
    
        if (!$sigHeader) {
            \Log::error('Stripe-Signature header ausente.');
            return response()->json(['error' => 'Missing Stripe-Signature header'], 400);
        }
    
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 403);
        }
    
        return response()->json(['status' => 'success']);
    }    
}
