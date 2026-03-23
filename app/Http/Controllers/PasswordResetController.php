<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;

class PasswordResetController extends Controller
{
    public function __construct(protected PasswordResetService $passwordResetService) {}

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $this->passwordResetService->forgotPassword($request->email);

            return response()->json([
                'message' => 'Te enviamos un enlace para restablecer tu contraseña',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword($request->validated());

            return response()->json([
                'message' => 'Contraseña actualizada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}