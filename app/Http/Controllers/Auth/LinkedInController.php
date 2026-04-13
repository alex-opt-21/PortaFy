<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\BienvenidaMail;
use Illuminate\Support\Facades\Mail;

class LinkedInController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('linkedin-openid')->redirect();
    }

    public function callback()
{
    try {
        $linkedinUser = Socialite::driver('linkedin-openid')->user();

        $user = Usuario::where('email', $linkedinUser->getEmail())
           ->where('proveedor', 'linkedin')  // FIX
           ->first();

        if (!$user) {
            $user = Usuario::create([
                'email'        => $linkedinUser->getEmail(),
                'nombre'       => $linkedinUser->user['given_name'] ?? $linkedinUser->getName() ?? 'Usuario',
                'apellido'     => $linkedinUser->user['family_name'] ?? '',
                'proveedor'    => 'linkedin',     // FIX
                'proveedor_id' => $linkedinUser->getId(), // FIX
                'rol'          => 'usuario',
                'password'     => bcrypt(Str::random(24)),
                'foto_perfil'  => $linkedinUser->getAvatar(),
            ]);
            Mail::to($user->email)->send(new BienvenidaMail($user));
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return view('linkedin-callback', ['token' => $token, 'user' => $user]);

    } catch (\Exception $e) {
        return view('linkedin-callback', ['error' => $e->getMessage()]);
    }
}
}
