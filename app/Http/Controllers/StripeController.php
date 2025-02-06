<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function index()
    {
        return view();
    }

    public function checkout()
    {
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
                        'unit_amount' => 100,
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

    public function success()
    {
        return view('index');
    }
}
