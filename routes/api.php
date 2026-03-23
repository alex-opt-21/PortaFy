<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
