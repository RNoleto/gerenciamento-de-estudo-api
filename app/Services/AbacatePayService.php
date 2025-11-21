<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AbacatePayService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.abacatepay.com/v1';
        $this->apiKey = env('ABACATEPAY_API_KEY');
    }

    public function createBilling($customerData, $amountCents)
    {
        $response = Http::withoutVerifying()
            ->withToken($this->apiKey)
            ->post("{$this->baseUrl}/billing/create", [
                'frequency' => 'ONE_TIME',
                'methods' => ['PIX'],
                'products' => [
                    [
                        'externalId' => 'estuday_premium',
                        'name' => 'Plano Premium Estuday (Mensal)',
                        'quantity' => 1,
                        'price' => $amountCents,
                    ]
                ],
                'returnUrl' => 'http://localhost:5173/checkout/sucesso', 
                'completionUrl' => 'http://localhost:5173/checkout/concluido',
                'customer' => [
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'taxId' => $customerData['cpf'], // OBRIGATÓRIO PARA PIX
                    'cellphone' => $customerData['phone'],
                ]
            ]);

        if ($response->failed()) {
            throw new \Exception('Erro AbacatePay: ' . $response->body());
        }

        return $response->json();
    }
}