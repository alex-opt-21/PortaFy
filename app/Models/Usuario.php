<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Profile;

class Usuario extends Authenticatable
{
    use HasApiTokens;
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'rol',
        'provider',
        'provider_id',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true;
    /*public function profile()
    {
        return $this->hasOne(Profile::class, 'usuario_id');
    }*/
}
