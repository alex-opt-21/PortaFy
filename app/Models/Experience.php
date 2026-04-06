<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $table = 'experience';
    protected $fillable = [
        'usuario_id', 'tipo', 'company',
        'title', 'descripcion',
        'fecha_inicio', 'fecha_fin',
    ];
}
