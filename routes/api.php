<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserCareerController;
use App\Http\Controllers\UserSubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStudyRecordController;
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
    Route::get('/{userStudyRecord}', [UserStudyRecordController::class, 'show'])->name('user-study-records.show');
    Route::put('/{userStudyRecord}', [UserStudyRecordController::class, 'update'])->name('user-study-records.update');
    Route::delete('/{userStudyRecord}', [UserStudyRecordController::class, 'destroy'])->name('user-study-records.destroy');
});