<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Str;
use App\Mail\BienvenidaMail;
use Illuminate\Support\Facades\Mail;

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

            $user = Usuario::where('email', $googleUser->getEmail())
               ->where('proveedor', 'google')
               ->first();

            if (!$user) {
                $user = Usuario::create([
                    'email'       => $googleUser->getEmail(),
                    'nombre'      => $googleUser->user['given_name'] ?? $googleUser->getName() ?? 'Usuario',
                    'apellido'    => $googleUser->user['family_name'] ?? '',
                    'proveedor'    => 'google',
                    'proveedor_id' => $googleUser->getId(),
                    'rol'         => 'usuario',
                    'password'    => bcrypt(Str::random(24)),
                    'foto_perfil' => $googleUser->getAvatar(),
                ]);
                Mail::to($user->email)->send(new BienvenidaMail($user));
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
