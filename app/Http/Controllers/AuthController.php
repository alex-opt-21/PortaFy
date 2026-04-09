<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UsuarioResource;
use Illuminate\Support\Facades\Http;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'message' => 'Sesión iniciada correctamente',
                'user'    => new UsuarioResource($result['user']),
                'token'   => $result['token'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function register(Request $request)
{
    try {
        Log::info('Request Register:', $request->all());
        Log::info('Recaptcha token:', ['token' => $request->recaptchaToken]);

        $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->recaptchaToken,
        ]);
        return response()->json($response->json());

        Log::info('Google reCAPTCHA Response:', $response->json());

        if (!$response->json('success')) {
            return response()->json(['message' => 'reCAPTCHA inválido'], 422);
        }

        $result = $this->authService->register($request->all());

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => new UsuarioResource($result['user']),
            'token'   => $result['token'],
        ], 201);

    } catch (\Exception $e) {
        Log::error('Register Error:', ['message' => $e->getMessage()]);
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}

}
