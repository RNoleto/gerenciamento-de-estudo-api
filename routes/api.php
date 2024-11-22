<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserCareerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Usuarios
Route::get('/users', [UserController::class, 'index']);
//Carreiras
Route::get('/careers', [CareerController::class, 'index']);
//Matérias
Route::get('/subjects', [SubjectController::class, 'index']);
//Rotas UserCareer
Route::prefix('user-career')->group(function () {
    Route::get('/', [UserCareerController::class, 'index']);
    Route::post('/', [UserCareerController::class, 'store']);
    Route::delete('/{id}', [UserCareerController::class, 'destroy']);
    Route::get('/{userId}', [UserCareerController::class, 'getUserCareer']);
});