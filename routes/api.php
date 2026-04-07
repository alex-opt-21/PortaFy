<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FormacionAcademicaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SocialController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::get('/users/search', [ProfileController::class, 'searchUsers']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/formacion', [FormacionAcademicaController::class, 'store']);
    Route::get('/formacion', [FormacionAcademicaController::class, 'index']);

    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills', [SkillController::class, 'store']);
    Route::put('/skills/{id}', [SkillController::class, 'update']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

    Route::get('/experience', [ExperienceController::class, 'index']);
    Route::post('/experience', [ExperienceController::class, 'store']);
    Route::put('/experience/{id}', [ExperienceController::class, 'update']);
    Route::delete('/experience/{id}', [ExperienceController::class, 'destroy']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    Route::get('/socials', [SocialController::class, 'index']);
    Route::post('/socials', [SocialController::class, 'store']);
    Route::put('/socials/{id}', [SocialController::class, 'update']);
    Route::delete('/socials/{id}', [SocialController::class, 'destroy']);

    // Obtener datos del perfil (GET)
    Route::get('/perfil/me', [ProfileController::class, 'show']);

    // Completar perfil inicial (POST)
    Route::post('/perfil/completar', [ProfileController::class, 'completar']);

    // Actualizar perfil existente (POST)
    Route::post('/perfil/actualizar', [ProfileController::class, 'storeOrUpdate']);

    // Si tus compañeros usan esta otra para el perfil profesional:
    Route::post('/perfil/profesional', [ProfileController::class, 'crearPerfilProfesional']);
});
