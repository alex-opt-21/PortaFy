<?php

namespace App\Services;

use App\Models\Usuario;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function forgotPassword(string $email): void
    {
        $usuario = Usuario::where('email', $email)->firstOrFail();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $usuario->notify(new ResetPasswordNotification($token));
    }

    public function resetPassword(array $data): void
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$record || !Hash::check($data['token'], $record->token)) {
            throw new \Exception('Token inválido o expirado');
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            throw new \Exception('El token ha expirado');
        }

        Usuario::where('email', $data['email'])->update([
            'password' => Hash::make($data['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
    }
}