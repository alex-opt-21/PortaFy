<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descripcion',
        'tecnologias',
        'url_repositorio',
        'url_demo',
        'imagen',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
