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

        /////

        'biografia',
        'fecha_nacimiento',
        'ubicacion',
        'foto_perfil',
        'foto_portada',
        'perfil_completado',
        'estado',
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true;
     public function formacionAcademica()
    {
        return $this->hasMany(FormacionAcademica::class, 'usuario_id');
    }
}
