<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormacionAcademica extends Model
{
    protected $table = 'formacion_academica';

    protected $fillable = [
        'usuario_id',
        'tipo_formacion',
        'institucion',
        'nombre_carrera',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
