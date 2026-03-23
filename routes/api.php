<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
// Agrupamos todo lo que requiere estar logueado
Route::middleware('auth:sanctum')->group(function () {

    // Obtener datos del perfil (GET)
    Route::get('/perfil/me', [ProfileController::class, 'show']);

    // Completar perfil inicial (POST)
    Route::post('/perfil/completar', [ProfileController::class, 'completar']);

    // Actualizar perfil existente (POST)
    Route::post('/perfil/actualizar', [ProfileController::class, 'storeOrUpdate']);

    // Si tus compañeros usan esta otra para el perfil profesional:
    Route::post('/perfil/profesional', [ProfileController::class, 'crearPerfilProfesional']);
});
