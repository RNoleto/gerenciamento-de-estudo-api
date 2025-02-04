<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\Stripecontroller@index')->name('index');
Route::post('/checkout', 'App\Http\Controllers\Stripecontroller@checkout')->name('checkout');
Route::get('/success', 'App\Http\Controllers\Stripecontroller@success')->name('success');

Route::get('/teste', function () {
    return 'Olá rota WEB teste';
});

Route::get('/forcar-erro', function () {
    abort(500);
});