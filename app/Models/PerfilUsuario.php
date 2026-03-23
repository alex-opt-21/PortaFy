<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilUsuario extends Model
{
    protected $table = 'perfiles_usuarios';

    protected $fillable = [
        'user_id', 'nombre', 'apellido', 'profesion',
        'universidad', 'ubicacion', 'fecha_nacimiento', 'foto_perfil'
    ];
}
