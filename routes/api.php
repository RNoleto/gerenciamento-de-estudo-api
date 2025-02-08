<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserCareerController;
use App\Http\Controllers\UserSubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStudyRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\Stripe;


use Illuminate\Support\Facades\Http;
use Stripe\Checkout\Session;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Usuarios
Route::get('/users', [UserController::class, 'index']);
//Carreiras
Route::get('/careers', [CareerController::class, 'index']);
Route::post('/careers', [CareerController::class, 'store']);
//Matérias
Route::get('/subjects', [SubjectController::class, 'index']);
//Rotas UserCareer
Route::prefix('user-career')->group(function () {
    Route::get('/', [UserCareerController::class, 'index']);
    Route::post('/', [UserCareerController::class, 'store']);
    Route::delete('/{id}', [UserCareerController::class, 'destroy']);
    Route::get('/{userId}', [UserCareerController::class, 'getUserCareer']);
    Route::get('/career_name/{user_id}', [UserCareerController::class, 'getCareerByUser']);
});

Route::prefix('user-subjects')->group(function () {
    Route::post('/', [UserSubjectController::class, 'store']);
    Route::get('/{userId}', [UserSubjectController::class, 'index']);
    Route::patch('/deactivate', [UserSubjectController::class, 'deactivate']);
});

Route::prefix('user-study-records')->group(function () {
    Route::get('/', [UserStudyRecordController::class, 'index'])->name('user-study-records.index');
    Route::post('/', [UserStudyRecordController::class, 'store'])->name('user-study-records.store');
    Route::get('/user/{userId}', [UserStudyRecordController::class, 'getUserRecords'])->name('user-study-records.getUserRecords');
    Route::get('/{userStudyRecord}', [UserStudyRecordController::class, 'show'])->name('user-study-records.show');
    Route::put('/{userStudyRecord}', [UserStudyRecordController::class, 'update'])->name('user-study-records.update');
    Route::delete('/{userStudyRecord}', [UserStudyRecordController::class, 'destroy'])->name('user-study-records.destroy');
});

Route::get('/teste', function () {
    return 'Olá rota API teste';
});

// Route::fallback(function () {
//     return response()->json(['message' => 'Not Found'], 404);
// });

// Teste de rotas para Stripe
// Route::get('/', [StripeController::class, 'index'])->name('index');
// Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');
// Route::get('/success', [StripeController::class, 'success'])->name('success');
// Route::post('/stripe/create-checkout', [StripeController::class, 'createCheckoutSession']);

// Route::get('/stripe/confirm-subscription', [StripeController::class, 'confirmSubscription']);

// Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);


// Rotas para o fluxo de pagamento com Stripe
Route::post('/stripe/create-checkout', [PaymentController::class, 'createCheckoutSession']);
Route::get('/stripe/confirm-subscription', [PaymentController::class, 'confirmSubscription']);
Route::post('/stripe/webhook', [PaymentController::class, 'confirmSubscription']);
Route::post('/webhook', [PaymentController::class, 'handleWebhook']);
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);
Route::get('/stripe/confirm', [PaymentController::class, 'confirmRedirect']);
