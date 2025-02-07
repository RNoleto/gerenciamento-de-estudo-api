<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/teste', function () {
    return 'Olá rota WEB teste';
});

Route::get('/forcar-erro', function () {
    abort(500);
});

// Route::get('/', [StripeController::class, 'index']);
// Route::post('/checkout', [StripeController::class, 'checkout']);
// Route::get('/success', [StripeController::class, 'success']);

Route::get('/teste-checkout', function (Request $request) {
    Stripe::setApiKey(config('stripe.sk'));

    $successUrl = env('FRONTEND_SUCCESS_URL', 'http://localhost:5173/area-do-aluno/pagamento-sucesso');
    $cancelUrl  = env('FRONTEND_CANCEL_URL', 'http://localhost:5173/area-do-aluno/pagamento-cancelado');

    Log::info("Testando Stripe Checkout: Success URL: $successUrl, Cancel URL: $cancelUrl");

    try {
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
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
        ]);

        return response()->json(['sessionId' => $session->id]);
    } catch (\Exception $e) {
        Log::error("Erro Stripe: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/teste-stripe', function () {
    try {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        return response()->json(['status' => 'Stripe carregado corretamente']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
