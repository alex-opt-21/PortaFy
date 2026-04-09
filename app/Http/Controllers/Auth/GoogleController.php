<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = Usuario::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = Usuario::create([
                    'email'       => $googleUser->getEmail(),
                    'nombre'      => $googleUser->user['given_name'] ?? $googleUser->getName() ?? 'Usuario',
                    'apellido'    => $googleUser->user['family_name'] ?? '',
                    'provider'    => 'google',
                    'provider_id' => $googleUser->getId(),
                    'rol'         => 'usuario',
                    'password'    => bcrypt(Str::random(24)),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return view('google-callback', [
                'token' => $token,
                'user'  => $user,
            ]);

        } catch (\Exception $e) {
            return view('google-callback', ['error' => $e->getMessage()]);
        }
    }
}
