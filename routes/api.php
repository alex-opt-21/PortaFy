<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::middleware('auth:sanctum')->group(function () {
Route::post('/perfil/completar', [ProfileController::class, 'completar']);
Route::post('/perfil-profesional', [ProfileController::class, 'crearPerfilProfesional']);

});
