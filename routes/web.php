<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LinkedInController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/auth/linkedin', [LinkedInController::class, 'redirect']);
Route::get('/auth/linkedin/callback', [LinkedInController::class, 'callback']);
Route::get('/auth/github', [GitHubController::class, 'redirect']);
Route::get('/auth/github/callback', [GitHubController::class, 'callback']);
Route::get('/auth/google', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
Route::get('/login', fn() => response()->json(['message' => 'Unauthorized'], 401))->name('login');
Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
