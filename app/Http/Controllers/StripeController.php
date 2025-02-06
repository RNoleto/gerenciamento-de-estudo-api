<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Certifique-se de importar o Http
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller {

    public function index() {
        return view();
    }

    public function checkout() {
        Stripe::setApiKey(config('stripe.sk'));

        $successUrl = env('FRONTEND_SUCCESS_URL');
        $cancelUrl = env('FRONTEND_CANCEL_URL');

        $session = Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'brl',
                        'product_data' => [
                            'name' => 'Send me money!!!',
                        ],
                        'unit_amount' => 100, // valor em centavos (R$1,00)
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function createCheckoutSession(Request $request) {
        // Obtenha o clerk_user_id enviado pelo frontend
        $clerkUserId = $request->input('clerk_user_id');
        if (!$clerkUserId) {
            return response()->json(['error' => 'clerk_user_id is required'], 400);
        }
    
        Stripe::setApiKey(config('stripe.sk'));
    
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'brl',
                    'product_data' => [
                        'name' => 'Plano Premium',
                    ],
                    'unit_amount' => 1000, // 10 reais (valor em centavos)
                    'recurring' => [
                        'interval' => 'month', // Recorrência mensal
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
            'metadata' => [ // Adicione metadados na sessão também
                'clerk_user_id' => $clerkUserId,
            ],
            // Use variáveis de ambiente que apontem para o seu frontend
            'success_url' => env('FRONTEND_SUCCESS_URL') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('FRONTEND_CANCEL_URL'),
        ]);
    
        return response()->json(['sessionId' => $session->id]);
    }

    public function confirmSubscription(Request $request) {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'Session ID is required'], 400);
        }

        Stripe::setApiKey(config('stripe.sk'));
        $session = Session::retrieve($sessionId);

        // Verifica se o pagamento foi concluído com sucesso
        if ($session->payment_status !== 'paid') {
            return response()->json(['error' => 'Payment not completed'], 400);
        }

        // Tente obter o clerk_user_id dos metadados da assinatura
        $clerkUserId = null;
        if (isset($session->subscription)) {
            // Para assinaturas, precisamos recuperar a assinatura para obter os metadados
            $subscription = \Stripe\Subscription::retrieve($session->subscription);
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

        return response()->json(['message' => 'Subscription confirmed']);
    }

    // Atualiza o usuário usando o clerk_user_id
    private function updateClerkUserById($clerkUserId) {
        $clerkApiKey = env('CLERK_API_KEY');
        $endpoint = "https://api.clerk.dev/v1/users/{$clerkUserId}";
    
        $updateResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $clerkApiKey,
            'Content-Type'  => 'application/json',
        ])->patch($endpoint, [
            'public_metadata' => ['subscriptionPlan' => 'premium']
        ]);
    
        // Log detalhado
        \Log::info('Clerk Update Response:', [
            'status' => $updateResponse->status(),
            'body' => $updateResponse->body()
        ]);
    
        return $updateResponse->successful();
    }

    // Atualiza o usuário usando o email (fallback)
    private function updateClerkUserByEmail($email) {
        $clerkApiKey = env('CLERK_API_KEY');
        $endpoint = "https://api.clerk.dev/v1/users";

        // Constrói a URL com o parâmetro de email
        $url = $endpoint . "?email_address=" . urlencode($email);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $clerkApiKey,
            'Content-Type'  => 'application/json',
        ])->get($url);

        \Log::info('Clerk GET response (by email)', $response->json());

        $userData = $response->json();
        // Se a resposta estiver encapsulada na chave "data", use-a
        if (isset($userData['data'])) {
            $users = $userData['data'];
        } else {
            $users = $userData;
        }

        if (!empty($users)) {
            if (isset($users[0]['id'])) {
                $userId = $users[0]['id'];
            } elseif (isset($users['id'])) {
                $userId = $users['id'];
            } else {
                \Log::error('Usuário não encontrado no Clerk', ['response' => $userData]);
                return false;
            }

            $updateResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $clerkApiKey,
                'Content-Type'  => 'application/json',
            ])->patch("$endpoint/$userId", [
                'public_metadata' => ['subscriptionPlan' => 'premium']
            ]);

            \Log::info('Clerk UPDATE (by email) response', $updateResponse->json());

            return $updateResponse->successful();
        } else {
            \Log::error('Nenhum dado retornado ao buscar usuário no Clerk para o email: ' . $email);
        }
        return false;
    }

    public function handleWebhook(Request $request) {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');
    
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 403);
        }
    
        // Processar o evento checkout.session.completed
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
    
            if ($session->payment_status === 'paid' && $session->mode === 'subscription') {
                $subscriptionId = $session->subscription;
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                $clerkUserId = $subscription->metadata->clerk_user_id ?? null;
    
                if ($clerkUserId) {
                    $this->updateClerkUserById($clerkUserId);
                } else {
                    $customerEmail = $session->customer_details->email;
                    $this->updateClerkUserByEmail($customerEmail);
                }
            }
        }
    
        return response()->json(['status' => 'success']);
    }

    public function success() {
        return view('index');
    }
}
