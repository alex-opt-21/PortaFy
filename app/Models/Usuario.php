<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
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

    // 🔹 RELACIÓN NECESARIA PARA LA BÚSQUEDA
    public function habilidades()
    {
        return $this->hasMany(\App\Models\Habilidad::class, 'usuario_id');
    }

    public function experiences()
    {
        return $this->hasMany(\App\Models\Experience::class, 'usuario_id');
    }

    public function proyectos()
    {
        return $this->hasMany(\App\Models\Proyecto::class, 'usuario_id');
    }

    public function sociales()
    {
        return $this->hasMany(\App\Models\Social::class, 'usuario_id');
    }

    /*public function profile()
    {
        return $this->hasOne(Profile::class, 'usuario_id');
    }*/

    public function formacionAcademica()
    {
        return $this->hasMany(FormacionAcademica::class, 'usuario_id');
    }

}
