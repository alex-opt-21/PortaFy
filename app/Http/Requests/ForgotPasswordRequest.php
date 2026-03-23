<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:usuarios,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio',
            'email.email'    => 'Correo inválido',
            'email.exists'   => 'No existe una cuenta con este correo',
        ];
    }
}