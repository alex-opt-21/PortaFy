<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    $captchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret'   => env('RECAPTCHA_SECRET_KEY'),
        'response' => $request->input('captcha_token'),
    ]);

    if (!$captchaResponse->json('success')) {
        return response()->json([
            'message' => 'Captcha inválido, intenta de nuevo.',
        ], 422);
    }

    try {
        $result = $this->authService->register($request->all());

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => new UsuarioResource($result['user']),
            'token'   => $result['token'],
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 400);
    }
}
}

