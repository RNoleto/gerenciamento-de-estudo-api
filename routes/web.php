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

Route::get('/', [StripeController::class, 'index']);
Route::post('/checkout', [StripeController::class, 'checkout']);
Route::get('/success', [StripeController::class, 'success']);