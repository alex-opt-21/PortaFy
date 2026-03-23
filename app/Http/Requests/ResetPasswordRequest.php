<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'    => 'El token es obligatorio',
            'email.required'    => 'El correo es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
            'password.min'      => 'Mínimo 8 caracteres',
            'password.confirmed'=> 'Las contraseñas no coinciden',
        ];
    }
}