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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/itajai', function () {
    return 'Olá rota web';
});

Route::get('/teste', function () {
    return 'Olá rota web tese';
});

//Carreiras
Route::get('/careers', [CareerController::class, 'index']);

//Matérias
Route::get('/subjects', [SubjectController::class, 'index']);