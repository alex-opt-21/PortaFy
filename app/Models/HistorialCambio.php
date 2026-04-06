<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialCambio extends Model
{
    protected $table = 'historial_cambios';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    protected $fillable = [
        'usuario_id', 'tabla_modificada',
        'campo_modificado', 'valor_anterior', 'valor_nuevo',
    ];
}
