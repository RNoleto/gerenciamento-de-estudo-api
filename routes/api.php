<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\ClerkController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserCareerController;
use App\Http\Controllers\UserSubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStudyRecordController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DailyProgressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\Stripe;
use App\Http\Controllers\Api\Admin\DashboardController;

use Illuminate\Support\Facades\Http;
use Stripe\Checkout\Session;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Rotas protegidas pelo Firebase Auth
Route::middleware('firebase.auth')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json(['uid' => $request->attributes->get('firebase_uid')]);
    });
});

//Rotas Administrativas, futuramente proteger com middleware de admin
Route::prefix('admin')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/charts/study-sessions', [DashboardController::class, 'getStudySessionsChartData']);
    Route::get('/charts/career-distribution', [DashboardController::class, 'getCareerDistributionChartData']);
});

// Rota para sincronizar usuário no banco de dados local/neon após o registro no Firebase
Route::post('/users/sync-on-register', [UserController::class, 'syncOnRegister'])->middleware('firebase.auth');

//Usuarios - Rotas para usar em Admin
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::put('/users/{user}', [UserController::class, 'update']);

//Carreiras
Route::get('/careers', [CareerController::class, 'index']);
Route::post('/careers', [CareerController::class, 'store']);

//Matérias
Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');

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

//cronograma
Route::get('/schedule/{userId}', [ScheduleController::class, 'getSchedule']);
Route::post('/schedule', [ScheduleController::class, 'saveSchedule']);

//Progresso Diário --> usado junto com o Cronograma
Route::get('/progress/{userId}', [DailyProgressController::class, 'getProgressForDate']);
Route::post('/progress/toggle', [DailyProgressController::class, 'toggleProgress']);
Route::post('/progress/sync', [DailyProgressController::class, 'syncProgress']);

// Route::get('/teste', function () {
//     return 'Olá rota API teste';
// });

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
Route::post('/update-clerk-metadata', [PaymentController::class, 'updateUserMetadata']);


//Rota para atualizar usuário no Clerk depois do pagamento
Route::post('/update-clerk-metadata', [ClerkController::class, 'updateUserMetadata']);


// Rota para enviar e-mail de suporte
Route::post('/support', [SupportController::class, 'sendSupport'])->name('support.send');