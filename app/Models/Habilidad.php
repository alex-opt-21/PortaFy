<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habilidad extends Model
{
    protected $table = 'habilidades';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'tipo',
        'nivel_cuantitativo',
        'nivel_cualitativo',
    ];

    // 🔹 Relación inversa (opcional pero recomendable)
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
