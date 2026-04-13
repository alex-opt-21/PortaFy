<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Str;
use App\Mail\BienvenidaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
{
    try {
        $githubUser = Socialite::driver('github')->user();

        $user = Usuario::where('email', $githubUser->getEmail())
           ->where('proveedor', 'github')  // FIX
           ->first();

        if (!$user) {
    $user = Usuario::create([
        'email'        => $githubUser->getEmail(),
        'nombre'       => $githubUser->getName() ?? $githubUser->getNickname() ?? 'Usuario',
        'apellido'     => '',
        'proveedor'    => 'github',
        'proveedor_id' => $githubUser->getId(),
        'rol'          => 'usuario',
        'password'     => bcrypt(Str::random(24)),
        'foto_perfil'  => $githubUser->getAvatar(),
    ]);


    try {
        Mail::to($user->email)->send(new BienvenidaMail($user));
    } catch (\Exception $mailError) {
        Log::error('Error enviando correo de bienvenida: ' . $mailError->getMessage());
    }
}

        $token = $user->createToken('auth_token')->plainTextToken;

        return view('github-callback', ['token' => $token, 'user' => $user]);

    } catch (\Exception $e) {
        return view('github-callback', ['error' => $e->getMessage()]);
    }
}
}
