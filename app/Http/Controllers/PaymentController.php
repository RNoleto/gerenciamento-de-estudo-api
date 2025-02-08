<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;


class PaymentController extends Controller
{
    /**
     * Cria uma sessão de checkout para assinatura premium.
     */
    public function createCheckoutSession(Request $request)
    {
        $clerkUserId = $request->input('clerk_user_id');
        if (!$clerkUserId) {
            return response()->json(['error' => 'clerk_user_id is required'], 400);
        }

        Stripe::setApiKey(env('STRIPE_SK'));

        $successUrl = env('FRONTEND_SUCCESS_URL');
        $cancelUrl  = env('FRONTEND_CANCEL_URL');

        Log::info('FRONTEND_SUCCESS_URL: ' . $successUrl);
        Log::info('FRONTEND_CANCEL_URL: ' . $cancelUrl);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'brl',
                    'product_data' => [
                        'name' => 'Plano Premium',
                    ],
                    'unit_amount' => 1990,
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
        // Se for GET (redirecionamento do usuário após o checkout)
        if ($request->isMethod('get')) {
            $sessionId = $request->query('session_id');
            if (!$sessionId) {
                return response()->json(['error' => 'Session ID is required'], 400);
            }

            // Recupera a sessão de checkout do Stripe
            Stripe::setApiKey(env('STRIPE_SK'));
            $session = Session::retrieve($sessionId);
            if (!$session || $session->payment_status !== 'paid') {
                return response()->json(['error' => 'Payment not completed or invalid session'], 400);
            }

            // Extraia os dados necessários da sessão
            $stripeCustomerId = $session->customer;            // Exemplo: "cus_Rjh7M4nuTCxYAy"
            $stripeSubscriptionId = $session->subscription;      // Exemplo: "sub_1QqDjRGA18YtxvXDhk2r2R3v"
            // O clerk_user_id está presente nos metadados da sessão
            $clerkUserId = $session->metadata->clerk_user_id ?? null;

            // NÃO atualizamos o Clerk; apenas retornamos os valores para debug.
            return response()->json([
                'message' => 'Redirecionamento confirmado.',
                'user' => [
                    'private_metadata' => [
                        'stripeCustomerId' => $stripeCustomerId,
                        'stripeSubscriptionId' => $stripeSubscriptionId,
                    ],
                    'public_metadata' => [
                        'subscriptionPlan' => 'premium'
                    ]
                ]
            ], 200);
        }

        // Se for POST, trata como webhook (código já existente)
        $payload = file_get_contents('php://input');
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        if (!$sigHeader) {
            Log::error('Stripe-Signature header ausente.');
            return response()->json(['error' => 'Missing Stripe-Signature header'], 400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SK'));
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid webhook signature: ' . $e->getMessage()], 403);
        }

        if ($event->type === 'checkout.session.completed') {
            return response()->json(['message' => 'Subscription confirmed via webhook'], 200);
        }

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
    public function handleWebhook(Request $request)
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        if (!$sigHeader) {
            Log::error('Stripe-Signature header ausente.');
            return response()->json(['error' => 'Missing Stripe-Signature header'], 400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SK'));
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid webhook signature: ' . $e->getMessage()], 403);
        }

        // Caso para o evento de fatura paga
        if ($event->type === 'invoice.paid') {
            $invoice = $event->data->object;
            // O campo "customer" da fatura contém o Stripe Customer ID
            $stripeCustomerId = $invoice->customer;
            // O campo "subscription" da fatura contém o Stripe Subscription ID (se aplicável)
            $stripeSubscriptionId = $invoice->subscription;

            // Tentativa de extrair o clerk_user_id
            // Exemplo: supondo que você tenha armazenado o clerk_user_id no metadata de um dos line_items
            $lineItems = $invoice->lines->data;
            $clerkUserId = null;
            if (!empty($lineItems) && isset($lineItems[0]->metadata->clerk_user_id)) {
                $clerkUserId = $lineItems[0]->metadata->clerk_user_id;
            }

            if ($clerkUserId) {
                $updated = $this->updateClerkUserByIdWithStripeInfo($clerkUserId, $stripeCustomerId, $stripeSubscriptionId);
                if (!$updated) {
                    Log::error('Falha ao atualizar usuário no Clerk para o clerk_user_id: ' . $clerkUserId);
                }
            } else {
                Log::error('clerk_user_id não encontrado no metadata dos line_items.');
            }
        }

        // Você pode manter outros cases ou retornar sucesso para eventos não tratados
        return response()->json(['message' => 'Webhook recebido com sucesso']);
    }

    /**
     * Atualiza o usuário no Clerk utilizando o clerk_user_id e envia os IDs do Stripe.
     */
    /**
     * Atualiza o usuário no Clerk utilizando o clerk_user_id e envia os IDs do Stripe.
     */
    private function updateClerkUserByIdWithStripeInfo($clerkUserId, $stripeCustomerId, $stripeSubscriptionId)
    {
        $clerkApiKey = env('CLERK_API_KEY');
        $endpoint = "https://api.clerk.dev/v1/users/{$clerkUserId}";

        $updateResponse = Http::withHeaders([
            'Authorization' => "Bearer {$clerkApiKey}",
            'Content-Type'  => 'application/json',
        ])->patch($endpoint, [
            'public_metadata' => ['subscriptionPlan' => 'premium'],
            'private_metadata' => [
                'stripeCustomerId' => $stripeCustomerId,
                'stripeSubscriptionId' => $stripeSubscriptionId,
            ],
        ]);

        Log::info('Clerk update response by ID:', [
            'status' => $updateResponse->status(),
            'body'   => $updateResponse->body()
        ]);

        return $updateResponse->successful();
    }



    public function confirmRedirect(Request $request)
    {
        // Esse endpoint é chamado via redirecionamento (GET) sem Stripe-Signature
        // Aqui você pode apenas buscar os dados do usuário atualizados (por exemplo, no Clerk)
        // ou exibir uma mensagem de sucesso.

        return response()->json(['message' => 'Pagamento confirmado. Os dados do usuário foram atualizados via webhook.']);
    }

    /**
     * Atualiza os metadados privados do usuário no Clerk.
     */
    public function updateUserMetadata(Request $request)
    {
        $clerkUserId = $request->input('clerk_user_id');
        $stripeCustomerId = $request->input('stripeCustomerId');
        $stripeSubscriptionId = $request->input('stripeSubscriptionId');

        if (!$clerkUserId) {
            return response()->json(['error' => 'clerk_user_id is required'], 400);
        }

        $clerkApiKey = env('CLERK_SECRET_KEY');
        $endpoint = "https://api.clerk.dev/v1/users/{$clerkUserId}";

        $updateResponse = Http::withHeaders([
            'Authorization' => "Bearer {$clerkApiKey}",
            'Content-Type'  => 'application/json',
        ])->patch($endpoint, [
            'private_metadata' => [
                'stripeCustomerId' => $stripeCustomerId,
                'stripeSubscriptionId' => $stripeSubscriptionId,
            ],
        ]);

        Log::info('Clerk update response:', [
            'status' => $updateResponse->status(),
            'body' => $updateResponse->body(),
        ]);

        if ($updateResponse->successful()) {
            return response()->json(['message' => 'User metadata updated successfully'], 200);
        } else {
            return response()->json(['error' => 'Failed to update user on Clerk'], 500);
        }
    }
}
